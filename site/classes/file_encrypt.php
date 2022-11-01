<?php
/**
* @package		com_usernotes
* @copyright	Copyright (C) 2015-2022 RJCreations. All rights reserved.
* @license		GNU General Public License version 3 or later; see LICENSE.txt
*/
defined('_JEXEC') or die;

define('FILE_ENCRYPTION_BLOCKS', 10000);

abstract class UserNotesFileEncrypt
{
	const METHOD = 'aes-256-ctr';

	public static function save ($key, $src, $dst)
	{
		$ivl = openssl_cipher_iv_length(self::METHOD);
		$iv = openssl_random_pseudo_bytes($ivl);

		$error = false;
		if ($fpOut = fopen($dst, 'w')) {
			// Put the initialzation vector to the beginning of the file
			fwrite($fpOut, $iv);
			if ($fpIn = fopen($src, 'rb')) {
				while (!feof($fpIn)) {
					$data = fread($fpIn, $ivl * FILE_ENCRYPTION_BLOCKS);
					$cdata = openssl_encrypt($data, self::METHOD, $key, OPENSSL_RAW_DATA, $iv);
					// Use the first xx bytes of the cipher as the next initialization vector
					$iv = substr($cdata, 0, $ivl);
					fwrite($fpOut, $cdata);
				}
				fclose($fpIn);
			} else {
				$error = true;
			}
			fclose($fpOut);
		} else {
			$error = true;
		}

		return $error ? null : $dst;
	}

	public static function output ($key, $src)
	{
		$ivl = openssl_cipher_iv_length(self::METHOD);
		$iv = openssl_random_pseudo_bytes($ivl);

		$error = false;
		if ($fpIn = fopen($src, 'rb')) {
			// Get the initialzation vector from the beginning of the file
			$iv = fread($fpIn, $ivl);
			while (!feof($fpIn)) {
				$cdata = fread($fpIn, $ivl * (FILE_ENCRYPTION_BLOCKS + 1)); // we have to read one block more for decrypting than for encrypting
				$data = openssl_decrypt($cdata, self::METHOD, $key, OPENSSL_RAW_DATA, $iv);
				// Use the first 16 bytes of the ciphertext as the next initialization vector
				$iv = substr($cdata, 0, $ivl);
				echo $data;
			}
			fclose($fpIn);
		} else {
			$error = true;
		}

		return $error ? null : $src;
	}

}