<?php

# vim: tabstop=4 shiftwidth=4 softtabstop=4

set_time_limit(0);

define('_ABSPATH', dirname(dirname(__FILE__)));
define('VERSION', '1.0');

require _ABSPATH.'/lib/Autoload.php';

class AccountModify {
	public $action;
	public $role;
	public $capability;

	const IDENT = __CLASS__;

	public function __construct() {
		$this->action = null;
		$this->role = null;
		$this->capability = null;
	}

	public function usage($value) {
		echo sprintf("This is role-acl %s\n", VERSION);
		echo "\n";
		echo "Usage:\n";
		echo "  role-acl.php [OPTIONS]\n";
		echo "\n";
		echo "General options:\n";
		echo "  --help		show this help, then exit\n";
		echo "  --version		output version information, then exit\n";
		echo "  --config		Config file to give to script with custom settings\n";
		echo "\n";
		echo "Role options:\n";
		echo "  --role		Role name or ID to modify\n";
		echo "  --capability		Capability to manipulate on the role\n";
		echo "\n";
		echo "Access options:\n";
		echo "  --allow		Allow the specified capability\n";
		echo "  --deny		Deny the specified capability\n";
		echo "\n";
		echo "Debugging options:\n";
		echo "  -d		Enable more verbose output\n";
		echo "\n";
		echo "Report bugs to <cst@fnal.gov>.\n";

		exit($value);
	}

	public function run() {
		$config = Ini_Config::getInstance();
		$db = App_Db::getInstance($config->database->default);

		if ($this->role === null) {
			throw new Exception('You must provide a role to manipulate');
		}

		if ($this->capability === null) {
			throw new Exception('You must specify a capability to allow or deny');
		}

		if ($this->action === null) {
			throw new Exception('You must specify whether to allow or deny the specified capability');
		}

		if (is_numeric($this->role)) {
			$roleId = $this->role;
		} else {
			$roleId = Role_Util::getId($this->role);
		}

		if ($roleId == 0) {
			throw new Exception('The specified role could not be found');
		}

		$role = new Role($roleId);

		$permissions = new Permissions;
		$permission = $permissions->get('Capability', $this->capability, 0, 1);
		if (empty($permission)) {
			throw new Exception('The specified capability could not be found');
		} else {
			$permissionId = $permission[0]['permission_id'];
		}

		switch($this->action) {
			case 'allow':
				$role->addPermission($permissionId);
				break;
			case 'deny':
				$role->deletePermission($permissionId);
				break;
			default:
				throw new Exception('The specified action was unknown');
		}
	}
}

function main() {
	$obj = new AccountModify;
	$cg = new Zend_Console_Getopt(
		array(
			'help|h'=> 'Display this help and exit',
			'run|r'	=> 'Run script',
			'role|a=s' => 'Role name or ID to manipulate',
			'capability|c=s' => 'Capability to manipulate on the role',
			'allow' => 'Allow the capability for the role',
			'deny' => 'Deny the capability on the role'
		)
	);

	try {
		$opts = $cg->parse();

		if (isset($opts->h)) {
			$obj->usage(0);
		}

		if (isset($opts->a)) {
			$obj->role = $opts->a;
		}

		if (isset($opts->c)) {
			$obj->capability = $opts->c;
		}

		if (isset($opts->allow)) {
			$obj->action = 'allow';
		} else if (isset($opts->deny)) {
			$obj->action = 'deny';
		}

		$obj->run();
	} catch (Zend_Console_Getopt_Exception $e) {
		print_r($e);
		echo "Failed to parse GetOpt parameters\n";
		$obj->usage(1);
	} catch (Exception $error) {
		echo sprintf("Whoops, hit an error. '%s'\n", $error->getMessage());
		$obj->usage(1);
	}
}

if ($argc > 0) {
	main();
}

?>
