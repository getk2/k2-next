<?php
/**
 * @version		3.0.0
 * @package		K2
 * @author		JoomlaWorks http://www.joomlaworks.net
 * @copyright	Copyright (c) 2006 - 2013 JoomlaWorks Ltd. All rights reserved.
 * @license		GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */

// no direct access
defined('_JEXEC') or die ;

// Auto load
require_once JPATH_ADMINISTRATOR.'/components/com_k2/vendor/autoload.php';

use Aws\S3\S3Client;

/**
 * K2 File class.
 * Uses the Gaufrette library
 */

class K2FileSystem
{

	protected static $instances = array();

	public static function getInstance($adapter = null)
	{
		if (is_null($adapter))
		{
			$adapter = 'Microsoft';
		}

		if (empty(self::$instances[$adapter]))
		{
			if ($adapter == 'Local')
			{
				$filesystem = new Gaufrette\Filesystem(new Gaufrette\Adapter\Local(JPATH_SITE));
				self::$instances[$adapter] = $filesystem;
			}
			elseif ($adapter == 'Amazon')
			{
				$service = S3Client::factory(array('key' => 'AKIAJSRRZ6DVIMDFY6MA', 'secret' => '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'));
				$filesystem = new Gaufrette\Filesystem(new Gaufrette\Adapter\AwsS3($service, 'k2fs'));
				self::$instances[$adapter] = $filesystem;
			}
			elseif ($adapter == 'Microsoft')
			{
				$connectionString = 'BlobEndpoint=http://k2fs.blob.core.windows.net/;AccountName=k2fs;AccountKey=VD7K8zb5y4LSFX6Im1/8whDX5JFhBn4Ze//ZnH+F5/9OzUQu7aYvZ660LD1usTm37wfHPCOHk0Jng9BbJSazTg==';
				$factory = new Gaufrette\Adapter\AzureBlobStorage\BlobProxyFactory($connectionString);
				$filesystem = new Gaufrette\Filesystem(new Gaufrette\Adapter\AzureBlobStorage($factory, 'k2files'));
				self::$instances[$adapter] = $filesystem;
			}

		}

		return self::$instances[$adapter];
	}

	public static function getURIRoot($pathonly = false, $adapter = null)
	{
		if (is_null($adapter))
		{
			$adapter = 'Microsoft';
		}
		if ($adapter == 'Local')
		{
			$root = ($pathonly) ? JURI::root($pathonly).'/' : JURI::root($pathonly);
		}
		elseif ($adapter == 'Amazon')
		{
			$root = 'https://k2fs.s3.amazonaws.com/';
		}
		elseif ($adapter == 'Microsoft')
		{
			$root = 'http://k2fs.blob.core.windows.net/k2files/';
		}
		return $root;
	}

	// We need a special function for writing image files because we need to set the content type correctly. This is not possible using Gaufrette for all adapters...
	public static function writeImageFile($key, $buffer)
	{
		$filesystem = self::getInstance();
		$adapter = $filesystem->getAdapter();
		// Add content header for Amazon S3
		if (method_exists($adapter, 'setMetadata'))
		{
			//$adapter->setMetadata($key, array('ContentType' => 'image/jpeg'));
		}
		$adapter->write($key, $buffer, true);
	}

}
