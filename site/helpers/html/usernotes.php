<?php
/**
 * @package    com_usernotes
 * @copyright  Copyright (C) 2016-2021 RJCreations - All rights reserved.
 * @license    GNU General Public License version 3 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;

abstract class JHtmlUsernotes
{

	public static function itemLink ($item)
	{
		$strate = '';
		if ($item->isParent) {
			$param = 'pid=';
		} else {
			$param = 'view=usernote&nid=';
			if ($item->vtotal) {
				$p = (int)($item->vtotal/$item->vcount/5*100);
				$strate = ' <div class="strate"><div class="strback"><div class="strating" style="width:'.$p.'%"></div></div></div>';
			}
		}
		$ttl = $item->secured ? base64_decode($item->title) : $item->title;
		$attrs = ['class'=>'nav'];
		if (isset($item->lPath)) $attrs['title'] = $item->lPath;
		return HTMLHelper::link(
				self::aiUrl($param.$item->itemID),
				'<div class="menug '.($item->isParent?'foldm':'docum').($item->secured?' isecure':'').'"></div>'.htmlspecialchars($ttl),
				$attrs
			) . $strate;
	}
	public static function prnActIcon ($id, $titl)
	{
		return HTMLHelper::link(
				self::aiUrl('task=printNote&nid='.$id),
				self::ico('icon-print large-icon'),
				['title'=>$titl, 'class'=>'nav act-left', 'onclick'=>'UNote.printNote(event,this);return false;']
			);
	}
	public static function newActIcon ($id, $titl)
	{
		return HTMLHelper::link(
				self::aiUrl('task=edit.addNote&pid='.$id),
				self::ico('icon-file-plus'),
				['title'=>$titl, 'class'=>'nav act-left']
			);
	}
	public static function edtActIcon ($id, $titl)
	{
		return HTMLHelper::link(
				self::aiUrl('task=edit.editNote&nid='.$id),
				self::ico('icon-edit'),
				['title'=>$titl, 'class'=>'nav act-left']
			);
	}
	public static function movActIcon ($id, $titl)
	{
		return '<a href="#" title="'.$titl.'" class="act-left" onclick="UNote.moveTo(event)">'.self::ico('icon-move').'</a>';
	}
	public static function attActIcon ($id, $titl)
	{
		return '<a href="#" title="'.$titl.'" class="act-left" onclick="UNote.addAttach(event)">'.self::ico('icon-attachment').'</a>';
	}
	public static function delActIcon ($id, $titl)
	{
		return HTMLHelper::link(
				self::aiUrl('task=edit.deleteItem&iid='.$id),
				self::ico('icon-file-minus idang'),
				['title'=>$titl, 'class'=>'nav act-right sure', 'data-suremsg'=>strtolower($titl)]
			);
	}
	public static function fNewActIcon ($id, $titl)
	{
		return HTMLHelper::link(
				self::aiUrl('task=edit.addFolder&type=f&pid='.$id),
				self::ico('icon-folder-plus-2'),
				['title'=>$titl, 'class'=>'nav act-left']
			);
	}
	public static function fEdtActIcon ($id, $titl)
	{
		return HTMLHelper::link(
				self::aiUrl('task=edit.editFolder&type=f&nid='.$id),
				self::ico('icon-edit'),
				['title'=>$titl, 'class'=>'nav act-right']
			);
	}
	public static function fDelActIcon ($id, $titl)
	{
		return HTMLHelper::link(
				self::aiUrl('task=edit.deleteItem&iid='.$id),
				self::ico('icon-folder-remove idang'),
				['title'=>$titl, 'class'=>'nav act-right sure', 'data-suremsg'=>strtolower($titl)]
			);
	}
	public static function toolActIcon ($id, $titl)
	{
		return '<a href="#" title="'.$titl.'" class="act-left" onclick="UNote.toolMenu(event);">'.self::ico('icon-wrench').'</a>';
	}


	public static function searchField ($pid)
	{
		$mnuId = self::mnuId();
		$fact = self::aiUrl('');
		return <<<EOD
<div class="search">
	<form name="sqry" action="{$fact}" method="GET" onsubmit="return UNote.performSearch(this,{$pid})">
		<input type="hidden" name="option" value="com_usernotes" />
		<input type="hidden" name="Itemid" value="{$mnuId}" />
		<input type="hidden" name="task" value="search" />
		<input type="search" name="sterm" results="10" autosave="user_notes" placeholder="Search..." />
	</form>
</div>
EOD;
	}


	public static function form_dropdown ($name = '', $options = [], $selected = [], $extra = '')
	{
		if (!is_array($selected)) {
			$selected = [$selected];
		}
		// If no selected state was submitted we will attempt to set it automatically
		if (count($selected) === 0) {
			// If the form name appears in the $_POST array we have a winner!
			if (isset($_POST[$name])) {
				$selected = [$_POST[$name]];
			}
		}
		if ($extra != '') $extra = ' '.$extra;
		$multiple = (count($selected) > 1 && strpos($extra, 'multiple') === FALSE) ? ' multiple="multiple"' : '';
		$form = '<select name="'.$name.'"'.$extra.$multiple.">\n";
		foreach ($options as $key => $val) {
			$key = (string) $key;
			if (is_array($val) && ! empty($val)) {
				$form .= '<optgroup label="'.$key.'">'."\n";
				foreach ($val as $optgroup_key => $optgroup_val) {
					$sel = (in_array($optgroup_key, $selected)) ? ' selected="selected"' : '';
					$form .= '<option value="'.$optgroup_key.'"'.$sel.'>'.(string) $optgroup_val."</option>\n";
				}
				$form .= '</optgroup>'."\n";
			} else {
				$sel = (in_array($key, $selected)) ? ' selected="selected"' : '';
				$form .= '<option value="'.$key.'"'.$sel.'>'.(string) $val."</option>\n";
			}
		}
		$form .= '</select>';
		return $form;
	}


	public static function form_button ($data = '', $content = '', $extra = '')
	{
		$defaults = ['name' => ((!is_array($data)) ? $data : ''), 'type' => 'button'];
		if (is_array($data) AND isset($data['content'])) {
			$content = $data['content'];
			unset($data['content']); // content is not an attribute
		}
		return "<button ".self::_parse_form_attributes($data, $defaults).$extra.">".$content."</button>";
	}


	public static function att_list ($atchs, $cid, $edt=false)
	{
		$html = '<span class="atchlbl">Attachments:</span>';
		foreach ($atchs as $atchr) {
			$atch = $atchr[0];
			if ($edt) {
				$html .= '<br /><span>';
				$html .= '<a href="#" title="'.Text::_('COM_USERNOTES_DEL_ATT').'" class="att-left" onclick="UNote.aj_delAttach(event,'.$cid.',\''.$atch.'\')">'.self::ico('icon-remove idang').'</a>';
				$html .= '<a href="#" title="'.Text::_('COM_USERNOTES_REN_ATT').'" class="att-left" onclick="UNote.aj_renAttach(event,'.$cid.',\''.$atch.'\')">'.self::ico('icon-pencil-2 idarn').'</a>';
				$html .= '&nbsp;'.$atch.'</span>';
			} else {
				$html .= '<div data-afile="'.rawurlencode($atch).'" class="atchlink">';
				$html .= '<a href="#" class="noeffect" onclick="UNote.getAttach(event,this,true)" title="'.Text::_('COM_USERNOTES_DOWNFIL').'">'.self::ico('icon-download').'</a><a href="#" class="noeffect" onclick="UNote.getAttach(event,this,false)" title="'.Text::_('COM_USERNOTES_VIEWFIL').'">'.$atch.'</a>';
				$html .= '</div>';
			}
		}
		return $html;
	}


/***** private functions *****/

	private static function _parse_form_attributes ($attributes, $default)
	{
		if (is_array($attributes)) {
			foreach ($default as $key => $val) {
				if (isset($attributes[$key])) {
					$default[$key] = $attributes[$key];
					unset($attributes[$key]);
				}
			}
			if (count($attributes) > 0) {
				$default = array_merge($default, $attributes);
			}
		}
		$att = '';
		foreach ($default as $key => $val) {
			if ($key == 'value') {
				$val = self::form_prep($val, $default['name']);
			}
			$att .= $key . '="' . $val . '" ';
		}
		return $att;
	}


	private static function aiUrl ($prms, $xml=true)
	{
		if (is_array($prms)) $prms = http_build_query($prms);
		$url = Route::_('index.php?option=com_usernotes'.($prms?('&'.$prms):'').'&Itemid='.self::mnuId(), $xml);
		return $url;
	}


	private static function mnuId ()
	{
		static $mnuId = 0;

		if (!$mnuId) {
			$mnuId = Factory::getApplication()->input->getInt('Itemid', 0);
		}
		return $mnuId;
	}


	private static function form_prep ($str = '', $field_name = '')
	{
		static $prepped_fields = [];

		// if the field name is an array we do this recursively
		if (is_array($str)) {
			foreach ($str as $key => $val) {
				$str[$key] = form_prep($val);
			}
			return $str;
		}
		if ($str === '') {
			return '';
		}

		// we've already prepped a field with this name
		// @todo need to figure out a way to namespace this so
		// that we know the *exact* field and not just one with
		// the same name
		if (isset($prepped_fields[$field_name])) {
			return $str;
		}

		$str = htmlspecialchars($str);

		// In case htmlspecialchars misses these.
		$str = str_replace(["'", '"'], ["&#39;", "&quot;"], $str);

		if ($field_name != '') {
			$prepped_fields[$field_name] = $field_name;
		}

		return $str;
	}


	private static function ico ($ico)
	{
		return '<i class="'.$ico.'"> </i>';
		return '<i class="'.$ico.'" style="font-size:'.(IS_SMALL_DEVICE ? 28 : 16).'px"> </i>';
	}


}