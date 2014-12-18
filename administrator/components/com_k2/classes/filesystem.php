<?php
/**
 * @version		3.0.0
 * @package		K2
 * @author		JoomlaWorks http://www.joomlaworks.net
 * @copyright	Copyright (c) 2006 - 2014 JoomlaWorks Ltd. All rights reserved.
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

	public static function getInstance($adapter = null, $localRoot = JPATH_SITE)
	{
		$params = JComponentHelper::getParams('com_k2');

		if (is_null($adapter))
		{
			$adapter = $params->get('filesystem', 'Local');
		}

		$key = $adapter.'|'.$localRoot;

		if (empty(self::$instances[$key]))
		{
			if ($adapter == 'Local')
			{
				$filesystem = new Gaufrette\Filesystem(new Gaufrette\Adapter\Local($localRoot));
				self::$instances[$key] = $filesystem;
			}
			elseif ($adapter == 'AmazonS3')
			{
				$AmazonS3AccessKey = $params->get('AmazonS3AccessKey');
				$AmazonS3SecretAccessKey = $params->get('AmazonS3SecretAccessKey');
				$AmazonS3Bucket = $params->get('AmazonS3Bucket');
				$service = S3Client::factory(array('key' => $AmazonS3AccessKey, 'secret' => $AmazonS3SecretAccessKey));
				$filesystem = new Gaufrette\Filesystem(new Gaufrette\Adapter\AwsS3($service, $AmazonS3Bucket));
				self::$instances[$key] = $filesystem;
			}
			elseif ($adapter == 'MicrosoftAzure')
			{
				$MicrosoftAzureEndpoint = $params->get('MicrosoftAzureEndpoint');
				$MicrosoftAzureAccountName = $params->get('MicrosoftAzureAccountName');
				$MicrosoftAzureAccountKey = $params->get('MicrosoftAzureAccountKey');
				$MicrosoftAzureContainer = $params->get('MicrosoftAzureContainer');
				$connectionString = 'BlobEndpoint='.$MicrosoftAzureEndpoint.'/;AccountName='.$MicrosoftAzureAccountName.';AccountKey='.$MicrosoftAzureAccountKey;
				$factory = new Gaufrette\Adapter\AzureBlobStorage\BlobProxyFactory($connectionString);
				$filesystem = new Gaufrette\Filesystem(new Gaufrette\Adapter\AzureBlobStorage($factory, $MicrosoftAzureContainer));
				self::$instances[$key] = $filesystem;
			}

		}

		return self::$instances[$key];
	}

	public static function getURIRoot($pathonly = false, $adapter = null)
	{
		$params = JComponentHelper::getParams('com_k2');

		if (is_null($adapter))
		{
			$adapter = $params->get('filesystem', 'Local');
		}
		if ($adapter == 'Local')
		{
			$root = ($pathonly) ? JURI::root($pathonly).'/' : JURI::root($pathonly);
		}
		elseif ($adapter == 'AmazonS3')
		{
			$root = 'https://'.$params->get('AmazonS3Bucket').'.s3.amazonaws.com/';
		}
		elseif ($adapter == 'MicrosoftAzure')
		{
			$root = $params->get('MicrosoftAzureEndpoint').'/'.$params->get('MicrosoftAzureContainer').'/';
		}
		return $root;
	}

	// We need a special function for writing image files because we need to set the content type correctly. This is not possible using Gaufrette for all adapters...
	public static function writeImageFile($key, $buffer)
	{
		$params = JComponentHelper::getParams('com_k2');
		$filesystem = self::getInstance();
		$adapter = $filesystem->getAdapter();
		// Add content header for Amazon S3
		if (method_exists($adapter, 'setMetadata') && $params->get('filesystem') == 'AmazonS3')
		{
			$adapter->setMetadata($key, array('ContentType' => 'image/jpeg'));
		}
		$adapter->write($key, $buffer, true);
	}

}
