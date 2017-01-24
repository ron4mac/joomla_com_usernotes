<?php
/**
 * @package    com_usernotes
 *
 * @copyright  Copyright (C) 2016 RJCreations - All rights reserved.
 * @license    GNU General Public License version 3 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

abstract class JHtmlUsernotes
{
	public static function itemLink ($item)
	{
		$param = $item->isParent ? 'pid=' : 'view=usernote&nid=';
		$ttl = $item->secured ? base64_decode($item->title) : $item->title;
		return JHtml::link(
				JRoute::_('index.php?option=com_usernotes&'.$param.$item->itemID),
				'<div class="menug '.($item->isParent?'foldm':'docum').($item->secured?' isecure':'').'"></div>'.htmlspecialchars($ttl),
				array('class'=>'nav')
			);
	}
	public static function prnActIcon ($id,$titl)
	{
		return JHtml::link(
				'index.php?option=com_usernotes&task=printNote&nid='.$id,
				self::ico('icon-print'),
				array('title'=>$titl,'class'=>'nav act-left','onclick'=>'printNote(event,this);return false;')
			);
	}
	public static function newActIcon ($id,$titl)
	{
		return JHtml::link(
				JRoute::_('index.php?option=com_usernotes&task=edit.addNote&pid='.$id),
				self::ico('icon-file-plus'),
				array('title'=>$titl,'class'=>'nav act-left')
			);
	}
	public static function edtActIcon ($id,$titl)
	{
		return JHtml::link(
				JRoute::_('index.php?option=com_usernotes&task=edit.editNote&nid='.$id),
				self::ico('icon-edit'),
				array('title'=>$titl,'class'=>'nav act-left')
			);
	}
	public static function movActIcon ($id,$titl)
	{
		return '<a href="#" title="Move note" class="act-left" onclick="moveTo(event)">'.self::ico('icon-move').'</a>';
	}
	public static function attActIcon ($id,$titl)
	{
		return '<a href="#" title="Add attachment" class="act-left" onclick="addAttach(event)">'.self::ico('icon-attachment').'</a>';
	}
	public static function delActIcon ($id,$titl)
	{
		return JHtml::link(
				JRoute::_('index.php?option=com_usernotes&task=edit.deleteItem&iid='.$id),
				self::ico('icon-file-minus idang'),
				array('title'=>$titl,'class'=>'nav act-right sure','data-suremsg'=>'delete this item')
			);
	}
	public static function fNewActIcon ($id,$titl)
	{
		return JHtml::link(
				JRoute::_('index.php?option=com_usernotes&task=edit.addFolder&type=f&pid='.$id),
				self::ico('icon-folder-plus-2'),
				array('title'=>$titl,'class'=>'nav act-left')
			);
	}
	public static function fEdtActIcon ($id,$titl)
	{
		return JHtml::link(
				JRoute::_('index.php?option=com_usernotes&task=edit.editFolder&type=f&nid='.$id),
				self::ico('icon-edit'),
				array('title'=>$titl,'class'=>'nav act-right')
			);
	}
	public static function fDelActIcon ($id,$titl)
	{
		return JHtml::link(
				JRoute::_('index.php?option=com_usernotes&task=edit.deleteItem&iid='.$id),
				self::ico('icon-folder-remove idang'),
				array('title'=>$titl,'class'=>'nav act-right sure','data-suremsg'=>'delete this folder')
			);
	}
	public static function toolActIcon ($id,$titl)
	{
		return '<a href="#" title="Utility tools" class="act-left" onclick="toolMenu(event);">'.self::ico('icon-wrench').'</a>';
	}
//	public static function srchActIcon ($id,$titl)
//	{
//		return '<a href="#" title="Search" class="dbsrch" onclick="return Oopim.performSearch(this.parentNode,'.$id.');">'.self::ico('icon-search').'</a>';
//	}

	public static function searchField ($pid)
	{
		return <<<EOD
<div class="search">
	<form name="sqry" onsubmit="return Oopim.performSearch(this,{$pid})">
		<input type="hidden" name="task" value="search" />
		<input type="search" name="sterm" results="10" autosave="oopim_notes" placeholder="Search..." />
	</form>
</div>
EOD;
	}

	public static function form_dropdown ($name = '', $options = array(), $selected = array(), $extra = '')
	{
		if (!is_array($selected)) {
			$selected = array($selected);
		}
		// If no selected state was submitted we will attempt to set it automatically
		if (count($selected) === 0) {
			// If the form name appears in the $_POST array we have a winner!
			if (isset($_POST[$name])) {
				$selected = array($_POST[$name]);
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
		$defaults = array('name' => ((!is_array($data)) ? $data : ''), 'type' => 'button');
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
				$html .= '<br /><span><img src="'.JUri::base().'/components/com_usernotes/static/imgs/deletex.png" alt="delete attachment" onclick="Oopim.aj_detach('.$cid.',\''.$atch.'\');" />&nbsp;'.$atch.'</span>';
			} else {
				$html .= '<div data-afile="'.rawurlencode($atch).'" class="atchlink">';
				$html .= '<a href="#" class="noeffect" onclick="getAttach(event,this,true)" title="download file"><div class="downlink">&nbsp;</div></a><a href="#" class="noeffect" onclick="getAttach(event,this,false)" title="view file">'.$atch.'</a>';
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

	private static function form_prep ($str = '', $field_name = '')
	{
		static $prepped_fields = array();

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
		$str = str_replace(array("'", '"'), array("&#39;", "&quot;"), $str);

		if ($field_name != '') {
			$prepped_fields[$field_name] = $field_name;
		}

		return $str;
	}

	private static function ico ($ico)
	{
		return '<i class="'.$ico.'" style="font-size:'.(IS_SMALL_DEVICE ? 28 : 16).'px"> </i>';
	}

}