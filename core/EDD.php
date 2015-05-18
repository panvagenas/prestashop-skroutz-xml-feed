<?php
/**
 * Project: module-core
 * File: EDD.php
 * User: Panagiotis Vagenas <pan.vagenas@gmail.com>
 * Date: 11/3/2015
 * Time: 10:36 μμ
 * Since: 150216
 * Copyright: 2015 Panagiotis Vagenas
 */

namespace XDaRk_v150216;


class EDD extends Core
{
	/**
	 * @return bool
	 * @throws \Exception
	 * @author Panagiotis Vagenas <pan.vagenas@gmail.com>
	 * @since 150216
	 */
	public function isEDD()
	{
		$storeUrl = $this->Options->getValue('edd.store_url', true);

		return $this->Options->getValue('edd.update', true) && $this->String->is_not_empty($storeUrl);
	}

	/**
	 * @return bool
	 * @throws \Exception
	 * @author Panagiotis Vagenas <pan.vagenas@gmail.com>
	 * @since 150216
	 */
	public function hasDemo()
	{
		return (bool)$this->Options->getValue('edd.demo', true);
	}

	/**
	 * @return bool
	 * @author Panagiotis Vagenas <pan.vagenas@gmail.com>
	 * @since 150216
	 */
	public function isDemoActive()
	{
		return $this->getDemoEndTime() >= time();
	}

	/**
	 * @return int
	 * @throws \Exception
	 * @author Panagiotis Vagenas <pan.vagenas@gmail.com>
	 * @since 150216
	 */
	public function getDemoEndTime()
	{
		$duration = (int)$this->Options->getValue('edd.demo_duration', true);
		$demo_start = (int)$this->Options->getValue('edd.demo_start');

		return $duration + $demo_start;
	}

	/**
	 * @return bool
	 * @author Panagiotis Vagenas <pan.vagenas@gmail.com>
	 * @since 150216
	 */
	public function isDemoOver()
	{
		return !$this->isDemoActive();
	}

	/**
	 * @param null $startTime
	 *
	 * @throws \Exception
	 * @author Panagiotis Vagenas <pan.vagenas@gmail.com>
	 * @since 150216
	 */
	public function startDemo($startTime = null)
	{
		if (!$this->hasDemo())
		{
			return;
		}
		if (!$startTime)
		{
			$startTime = time();
		}
		$demo_start = (int)$this->Options->getValue('edd.demo_start');
		if ($demo_start === 0)
		{
			$this->Options->saveOptions(array('edd.demo_start' => $startTime));
		}
	}

	/**
	 * @author Panagiotis Vagenas <pan.vagenas@gmail.com>
	 * @since 150216
	 */
	public function endDemo()
	{
		$this->Options->saveOptions(array('edd.demo_start' => 0));
	}

	/**
	 * @return mixed
	 * @throws \Exception
	 * @author Panagiotis Vagenas <pan.vagenas@gmail.com>
	 * @since 150216
	 */
	public function getLicense()
	{
		return $this->Options->getValue('edd_license');
	}

	/**
	 * @param bool $overrideIfInDemo
	 *
	 * @return bool|mixed
	 * @throws \Exception
	 * @author Panagiotis Vagenas <pan.vagenas@gmail.com>
	 * @since 150216
	 */
	public function getLicenseStatus($overrideIfInDemo = true)
	{
		if ($overrideIfInDemo && $this->hasDemo() && $this->isDemoActive())
		{
			return true;
		}

		return $this->Options->getValue('edd.license.status');
	}

	/**
	 * @param $license
	 *
	 * @author Panagiotis Vagenas <pan.vagenas@gmail.com>
	 * @since 150216
	 */
	public function setLicense($license)
	{
		$this->Options->saveOptions(array('edd_license' => $license));
	}

	/**
	 * @param $status
	 *
	 * @author Panagiotis Vagenas <pan.vagenas@gmail.com>
	 * @since 150216
	 */
	public function setLicenseStatus($status)
	{
		$status = (bool)$status ? 1 : 0;
		$this->Options->saveOptions(array('edd.license.status' => $status));
	}

	/**
	 * @return int
	 * @author Panagiotis Vagenas <pan.vagenas@gmail.com>
	 * @since 150216
	 */
	public function chkLicense()
	{
		$license_data = $this->APIRequest();

		if (!is_object($license_data) || !isset($license_data->license))
		{
			return 2;
		}

		if ($license_data->license == 'valid')
		{
			return 1;
		} else
		{
			return 0;
		}
	}

	/**
	 * @param $license
	 *
	 * @return bool|mixed
	 * @throws Exception
	 * @author Panagiotis Vagenas <pan.vagenas@gmail.com>
	 * @since 150216
	 */
	public function activateLicense($license)
	{
		$this->check_arg_types('string', func_get_args());

		$license_data = $this->APIRequest('activate_license', $license);
		if (is_object($license_data))
		{
			if ($license_data->license === 'valid')
			{
				$this->setLicense($license);
				$this->setLicenseStatus(1);
				// TODO The notice
//				$this->©notice->enqueue( array(
//					'notice'           => $this->__( 'License activated!' ),
//					'allow_dismissals' => false
//				) );
			} elseif ($license_data->success == false && $license_data->error == 'expired')
			{
				$this->setLicense($license);
				$this->setLicenseStatus(0);
				// TODO The notice
//				$this->©notice->error_enqueue( array(
//					'notice'           => $this->__( 'Your license has expired' ),
//					'allow_dismissals' => false
//				) );
			} else
			{
				$this->setLicense($license);
				$this->setLicenseStatus(0);
				// TODO The notice
//				$this->©notice->error_enqueue( array(
//					'notice'           => $this->__( 'License couldn\'t be activated. Please check your input.' ),
//					'allow_dismissals' => false
//				) );
			}

			return $license_data;
		} else
		{
			// TODO The notice
//			$this->©notice->error_enqueue( array(
//				'notice'           => $this->__( 'There was an error contacting the license server. Please try again later.' ),
//				'allow_dismissals' => false
//			) );
			$this->setLicenseStatus(0);

			return false;
		}
	}

	/**
	 * @param $license
	 *
	 * @return bool|mixed
	 * @author Panagiotis Vagenas <pan.vagenas@gmail.com>
	 * @since 150216
	 */
	public function deactivateLicense($license)
	{
		$license_data = $this->APIRequest('deactivate_license', $license);
		if (is_object($license_data))
		{
			if ($license_data->license == 'deactivated')
			{
				$this->setLicenseStatus(0);
				// TODO The notice
//				$this->©notice->enqueue( array(
//					'notice'           => $this->__( 'License deactivated!' ),
//					'allow_dismissals' => false
//				) );
			}

			return $license_data;
		}

		return false;
	}

	/**
	 * TODO Implement curl use in case fopen not allowed
	 *
	 * @param string $action
	 * @param string $license
	 *
	 * @return mixed
	 * @author Panagiotis Vagenas <pan.vagenas@gmail.com>
	 * @since 150216
	 */
	public function APIRequest($action = 'check_license', $license = '')
	{
		$stream_context = @stream_context_create(
			array(
				'http' => array(
					'timeout' => 30,
					'method'  => 'GET',
				),
				'ssl'  => array(
					'verify_peer' => false
				)
			)
		);

		$api_params = array(
			'edd_action' => $action,
			'license'    => empty($license) ? $this->getLicense() : $license,
			'item_name'  => $this->moduleInstance->displayName,
			'url'        => $this->Url->getBaseUrl(true, false)
		);

		$url = 'https://erp.xdark.eu?'.http_build_query($api_params);

		$res = @file_get_contents($url, null, $stream_context);

		return json_decode($res);
	}
}