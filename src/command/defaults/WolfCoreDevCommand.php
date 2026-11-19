<?php
/*
 * WolfCore-MP native developer commands.
 * Adapted from PocketMine/DevTools 1.17.2 (LGPL-3.0-or-later).
 * These are core commands: no DevTools plugin is loaded or registered.
 */
declare(strict_types=1);

namespace wolfcore\command\defaults;

use wolfcore\command\CommandSender;
use wolfcore\command\utils\InvalidCommandSyntaxException;
use wolfcore\event\EventPriority;
use wolfcore\event\HandlerListManager;
use wolfcore\permission\PermissionAttachmentInfo;
use wolfcore\permission\PermissionManager;
use wolfcore\player\Player;
use wolfcore\plugin\FolderPluginLoader;
use wolfcore\plugin\PharPluginLoader;
use wolfcore\plugin\Plugin;
use wolfcore\plugin\PluginBase;
use wolfcore\utils\TextFormat;
use wolfcore\utils\Utils;
use Symfony\Component\Filesystem\Path;

final class WolfCoreDevCommand extends VanillaCommand{
	private const OPTIONS = [
		'makeplugin' => ['Build a PHAR from a folder plugin', '/makeplugin <name|*>'],
		'extractplugin' => ['Extract a PHAR plugin to source', '/extractplugin <name>'],
		'genplugin' => ['Generate a new folder plugin', '/genplugin <name> <author>'],
		'checkperm' => ['Inspect permission node and value', '/checkperm <node> [player]'],
		'listperms' => ['List effective permissions', '/listperms [player]'],
		'handlers' => ['List registered event handlers', '/handlers [event-filter]'],
		'handlersbyplugin' => ['List plugin event handlers', '/handlersbyplugin <plugin>'],
	];

	public function __construct(string $name){
		if(!isset(self::OPTIONS[$name])){
			throw new \InvalidArgumentException('Unknown developer command: ' . $name);
		}
		parent::__construct($name, self::OPTIONS[$name][0], self::OPTIONS[$name][1]);
		$this->setPermission('wolfcore.command.' . $name);
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args) : bool{
		try{
			return match($this->getName()){
				'makeplugin' => $this->makePlugin($sender, $args),
				'extractplugin' => $this->extractPlugin($sender, $args),
				'genplugin' => $this->generatePlugin($sender, $args),
				'checkperm' => $this->checkPermission($sender, $args),
				'listperms' => $this->listPermissions($sender, $args),
				'handlers' => $this->handlers($sender, $args),
				'handlersbyplugin' => $this->handlersByPlugin($sender, $args),
			};
		}catch(\PharException|\RuntimeException|\UnexpectedValueException $e){
			$sender->sendMessage(TextFormat::RED . 'WolfCore developer command failed: ' . $e->getMessage());
			return true;
		}
	}

	private function outputFolder(CommandSender $sender) : string{
		$path = Path::join($sender->getServer()->getDataPath(), 'plugin_builds');
		if(!is_dir($path) && !mkdir($path, 0755, true) && !is_dir($path)){
			throw new \RuntimeException('Cannot create ' . $path);
		}
		return $path;
	}

	private function getPluginPath(Plugin $plugin) : string{
		$property = new \ReflectionProperty(PluginBase::class, 'file');
		return rtrim((string) $property->getValue($plugin), "\\/");
	}

	private function findPlugin(CommandSender $sender, array $args) : ?Plugin{
		if($args === []){
			throw new InvalidCommandSyntaxException();
		}
		$name = trim(implode(' ', $args));
		$plugin = $sender->getServer()->getPluginManager()->getPlugin($name);
		if($plugin === null){
			$sender->sendMessage(TextFormat::RED . 'Plugin not found: ' . $name);
		}
		return $plugin;
	}

	private function buildPlugin(CommandSender $sender, Plugin $plugin) : bool{
		if(!$plugin->getPluginLoader() instanceof FolderPluginLoader){
			$sender->sendMessage(TextFormat::RED . $plugin->getName() . ' is not a folder plugin');
			return false;
		}
		if(ini_get('phar.readonly') !== '0'){
			$sender->sendMessage(TextFormat::RED . 'Set phar.readonly=0 in php.ini to use /makeplugin');
			return false;
		}
		$description = $plugin->getDescription();
		$source = realpath($this->getPluginPath($plugin));
		if($source === false || !is_file($source . '/plugin.yml')){
			throw new \RuntimeException('Plugin source directory unavailable');
		}
		if(preg_match('/^[A-Za-z0-9_.-]+$/D', $description->getName()) !== 1 || preg_match('/^[A-Za-z0-9_.-]+$/D', $description->getVersion()) !== 1){
			throw new \RuntimeException('Unsafe plugin name or version for output filename');
		}
		$target = Path::join($this->outputFolder($sender), $description->getName() . '_v' . $description->getVersion() . '.phar');
		if(file_exists($target)){
			\Phar::unlinkArchive($target);
		}
		$phar = new \Phar($target);
		$phar->startBuffering();
		$phar->setStub("<?php __HALT_COMPILER();");
		$phar->setMetadata([
			'name' => $description->getName(),
			'version' => $description->getVersion(),
			'main' => $description->getMain(),
			'api' => $sender->getServer()->getApiVersion()
		]);
		$it = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($source, \FilesystemIterator::SKIP_DOTS));
		$count = 0;
		foreach($it as $info){
			if(!$info->isFile() || $info->isLink()){
				continue;
			}
			$relative = str_replace('\\', '/', substr($info->getPathname(), strlen($source) + 1));
			$parts = explode('/', $relative);
			if(in_array('tests', $parts, true) || array_filter($parts, static fn(string $part) : bool => str_starts_with($part, '.')) !== []){
				continue;
			}
			$phar->addFile($info->getPathname(), $relative);
			$count++;
		}
		if($count === 0){
			throw new \RuntimeException('No plugin files found');
		}
		$phar->setSignatureAlgorithm(\Phar::SHA256);
		$phar->stopBuffering();
		$sender->sendMessage(TextFormat::GREEN . 'Built ' . $count . ' files: ' . $target);
		return true;
	}

	private function makePlugin(CommandSender $sender, array $args) : bool{
		if($args === ['*']){
			$ok = 0;
			foreach($sender->getServer()->getPluginManager()->getPlugins() as $plugin){
				if($plugin->getPluginLoader() instanceof FolderPluginLoader && $this->buildPlugin($sender, $plugin)){
					$ok++;
				}
			}
			$sender->sendMessage('Built ' . $ok . ' folder plugins');
			return true;
		}
		$plugin = $this->findPlugin($sender, $args);
		return $plugin === null || $this->buildPlugin($sender, $plugin);
	}

	private function extractPlugin(CommandSender $sender, array $args) : bool{
		$plugin = $this->findPlugin($sender, $args);
		if($plugin === null){
			return true;
		}
		if(!$plugin->getPluginLoader() instanceof PharPluginLoader){
			$sender->sendMessage(TextFormat::RED . $plugin->getName() . ' is not a PHAR plugin');
			return true;
		}
		$description = $plugin->getDescription();
		if(preg_match('/^[A-Za-z0-9_.-]+$/D', $description->getName()) !== 1 || preg_match('/^[A-Za-z0-9_.-]+$/D', $description->getVersion()) !== 1){
			throw new \RuntimeException('Unsafe plugin name or version for output filename');
		}
		$path = $this->getPluginPath($plugin);
		if(!str_starts_with($path, 'phar://')){
			throw new \RuntimeException('Unexpected PHAR plugin path');
		}
		$target = Path::join($this->outputFolder($sender), $description->getName() . '_v' . $description->getVersion());
		if(file_exists($target)){
			throw new \RuntimeException('Output folder exists: ' . $target);
		}
		(new \Phar(substr($path, strlen('phar://'))))->extractTo($target);
		$sender->sendMessage(TextFormat::GREEN . 'Extracted plugin to ' . $target);
		return true;
	}

	private function generatePlugin(CommandSender $sender, array $args) : bool{
		if(count($args) !== 2){
			throw new InvalidCommandSyntaxException();
		}
		[$name, $author] = $args;
		foreach([$name, $author] as $part){
			if(preg_match('/^[A-Za-z0-9_-]+$/D', $part) !== 1){
				$sender->sendMessage(TextFormat::RED . 'Name and author may contain only A-Z, 0-9, _ and -');
				return true;
			}
		}
		$path = Path::join($sender->getServer()->getPluginPath(), $name);
		if(file_exists($path) || $sender->getServer()->getPluginManager()->getPlugin($name) !== null){
			$sender->sendMessage(TextFormat::RED . 'Plugin already exists: ' . $name);
			return true;
		}
		$fix = static function(string $part) : string{
			$part = str_replace('-', '_', $part);
			return ctype_digit($part[0]) ? '_' . $part : $part;
		};
		$namespace = $fix($author) . '\\' . $fix($name);
		if(!mkdir($path . '/src', 0755, true) && !is_dir($path . '/src')){
			throw new \RuntimeException('Cannot create plugin source directory');
		}
		file_put_contents($path . '/plugin.yml', yaml_emit([
			'name' => $name,
			'version' => '0.0.1',
			'main' => $namespace . '\\Main',
			'api' => $sender->getServer()->getApiVersion(),
			'src-namespace-prefix' => $namespace,
			'author' => $author
		]));
		file_put_contents($path . '/src/Main.php', "<?php\ndeclare(strict_types=1);\n\nnamespace " . $namespace . ";\n\nuse wolfcore\\plugin\\PluginBase;\n\nfinal class Main extends PluginBase{\n    protected function onEnable() : void{\n        \$this->getLogger()->info('Plugin enabled');\n    }\n}\n");
		$sender->sendMessage(TextFormat::GREEN . 'Generated folder plugin: ' . $path);
		return true;
	}

	private function checkPermission(CommandSender $sender, array $args) : bool{
		if(count($args) < 1 || count($args) > 2){
			throw new InvalidCommandSyntaxException();
		}
		$target = $sender;
		if(isset($args[1])){
			$target = $sender->getServer()->getPlayerByPrefix($args[1]);
			if($target === null){
				$sender->sendMessage(TextFormat::RED . 'Player not found');
				return true;
			}
			if($target !== $sender && !$sender->hasPermission('wolfcore.command.listperms')){
				$sender->sendMessage(TextFormat::RED . 'No permission to inspect another player');
				return true;
			}
		}
		$node = strtolower($args[0]);
		$permission = PermissionManager::getInstance()->getPermission($node);
		$sender->sendMessage('Permission ' . $node . ': ' . ($permission === null ? 'not registered' : 'registered') . '; ' . $target->getName() . ': ' . ($target->hasPermission($node) ? 'true' : 'false'));
		if($permission !== null){
			$sender->sendMessage('Children: ' . implode(', ', array_keys($permission->getChildren())));
		}
		return true;
	}

	private function listPermissions(CommandSender $sender, array $args) : bool{
		if(count($args) > 1){
			throw new InvalidCommandSyntaxException();
		}
		$target = $sender;
		if(isset($args[0])){
			$target = $sender->getServer()->getPlayerByPrefix($args[0]);
			if($target === null){
				$sender->sendMessage(TextFormat::RED . 'Player not found');
				return true;
			}
		}
		$sender->sendMessage('Effective permissions for ' . $target->getName() . ':');
		foreach($target->getEffectivePermissions() as $name => $info){
			$sender->sendMessage('- ' . $name . ': ' . ($info->getValue() ? 'true' : 'false'));
		}
		return true;
	}

	private function describeHandler(CommandSender $sender, string $eventClass, $handler, int $priority) : void{
		$sender->sendMessage('- ' . $eventClass . ' | ' . $handler->getPlugin()->getName() . ' | ' . Utils::getNiceClosureName($handler->getHandler()) . ' | priority ' . $priority);
	}

	private function handlers(CommandSender $sender, array $args) : bool{
		if(count($args) > 1){
			throw new InvalidCommandSyntaxException();
		}
		$found = 0;
		foreach(HandlerListManager::global()->getAll() as $eventClass => $list){
			if(isset($args[0]) && !str_contains($eventClass, $args[0])){
				continue;
			}
			foreach(EventPriority::ALL as $priority){
				foreach($list->getListenersByPriority($priority) as $handler){
					$this->describeHandler($sender, $eventClass, $handler, $priority);
					$found++;
				}
			}
		}
		$sender->sendMessage('Found ' . $found . ' event handlers');
		return true;
	}

	private function handlersByPlugin(CommandSender $sender, array $args) : bool{
		$plugin = $this->findPlugin($sender, $args);
		if($plugin === null){
			return true;
		}
		$found = 0;
		foreach(HandlerListManager::global()->getAll() as $eventClass => $list){
			foreach(EventPriority::ALL as $priority){
				foreach($list->getListenersByPriority($priority) as $handler){
					if($handler->getPlugin() === $plugin){
						$this->describeHandler($sender, $eventClass, $handler, $priority);
						$found++;
					}
				}
			}
		}
		$sender->sendMessage('Found ' . $found . ' event handlers for ' . $plugin->getName());
		return true;
	}
}
