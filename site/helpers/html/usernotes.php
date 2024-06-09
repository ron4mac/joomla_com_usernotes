<?php
/**
* @package		com_usernotes
* @copyright	Copyright (C) 2015-2024 RJCreations. All rights reserved.
* @license		GNU General Public License version 3 or later; see LICENSE.txt
* @since		1.4.3
*/
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;

abstract class HtmlUsernotes
{
	protected static $instanceObj = null;

	public static function setInstance (Object $obj)
	{
		self::$instanceObj = $obj;
	}

	public static function itemStars ($item)
	{
		$p = $item->vcount ? (int)($item->vtotal/$item->vcount/5*100) : 0;
		return ' <div class="strate"><div class="strback"><div class="strating" style="width:'.$p.'%"></div></div></div>';
	}
	public static function itemLink ($item, $ratings=false)
	{
		$strate = '';
		if ($item->isParent) {
			$param = 'pid=';
		} else {
			$param = 'view=usernote&nid=';
			if ($ratings && $item->vtotal) {
				$strate = self::itemStars($item);
			}
		}
		$ttl = $item->secured ? base64_decode($item->title) : $item->title;
		$attrs = ['class'=>'act'];
		if (isset($item->lPath)) $attrs['title'] = $item->lPath;
		return HTMLHelper::link(
				self::aiUrl($param.$item->itemID),
				'<div class="itml '.($item->isParent?'foldm':'docum').($item->secured?' isecure':'').'">'.htmlspecialchars($ttl).'</div>',
			//	htmlspecialchars($ttl),
				$attrs
			) . $strate;
	}
	public static function itemBLink ($item, $ratings=false)
	{
		$strate = '';
		$iclass = 'docum';
		if ($item->isParent) {
			$iclass = 'foldm';
			$param = 'pid=';
		} else {
			$param = 'view=usernote&nid=';
			if ($ratings && $item->vtotal) {
				$strate = self::itemStars($item);
			}
		}
		$ttl = $item->secured ? base64_decode($item->title) : $item->title;
		$attrs = ['class'=>'act'];
		if (isset($item->lPath)) $attrs['title'] = $item->lPath;
	//	return '<div class="itml '.$iclass.($item->secured?' isecure':'').'"><button class="link2" data-href="'.self::aiUrl($param.$item->itemID).'">'.$ttl.'</button></div>'.$strate;
		return '<div class="itml '.$iclass.($item->secured?' isecure':'').'" data-href="'.self::aiUrl($param.$item->itemID).'"><button class="link2">'.$ttl.'</button></div>'.$strate;

		return HTMLHelper::link(
				self::aiUrl($param.$item->itemID),
				'<div class="itml '.($item->isParent?'foldm':'docum').($item->secured?' isecure':'').'">'.htmlspecialchars($ttl).'</div>',
			//	htmlspecialchars($ttl),
				$attrs
			) . $strate;
	}
	public static function itemQview ($item, $ratings=false)
	{
		if ($item->isParent || $item->secured) return self::itemBLink($item, $ratings);

		$strate = '';
		if ($item->cmntcnt) {
			$strate .= '<i class="far fa-comments cmnticn"> </i>';
		}
		if ($ratings && $item->vtotal) {
			$strate .= self::itemStars($item);
		}

		$ttl = $item->secured ? base64_decode($item->title) : $item->title;
	//	return '<div class="itml docum'.($item->secured?' isecure':'').'"><button class="qview" data-href="'.self::aiUrl('view=usernote&nid='.$item->itemID).'">'.$ttl.'</button></div>'.$strate;
		return '<div class="itml docum'.($item->secured?' isecure':'').'" data-href="'.self::aiUrl('view=usernote&nid='.$item->itemID).'"><button class="qview">'.$ttl.'</button></div>'.$strate;
	}
	public static function prnActIcon ($id, $titl)
	{
		return HTMLHelper::link(
				self::aiUrl('task=printNote&nid='.$id),
				self::ico('pr','large-icon'),
				['title'=>$titl, 'class'=>'act act-left', 'onclick'=>'UNote.printNote(event,this);return false;']
			);
	}
	public static function cmntActIcon ($id, $titl, $has=0, $upd=false)
	{
		list($icnv,$xclass,$task) = $has ? ['cmm',' hasem','cmntDisp'] : ['cm','','cmntNote'];
		$icon = self::ico($icnv,'large-icon'.$xclass);
		if ($upd) return $icon;
		return HTMLHelper::link(
				self::aiUrl('task='.$task.'&nid='.$id),
				$icon,
				['title'=>$titl, 'class'=>'act act-left', 'onclick'=>'UNote.cmntNote(event,this);return false;']
			);
	}
	public static function newActIcon ($id, $titl)
	{
		return HTMLHelper::link(
				self::aiUrl('task=edit.addNote&pid='.$id),
				self::ico('nn'),
				['title'=>$titl, 'class'=>'act act-left']
			);
	}
	public static function edtActIcon ($id, $titl)
	{
		return HTMLHelper::link(
				self::aiUrl('task=edit.editNote&nid='.$id),
				self::ico('en'),
				['title'=>$titl, 'class'=>'act act-left']
			);
	}
	public static function movActIcon ($id, $titl)
	{
		return '<a href="javascript:void(0);" title="'.$titl.'" class="act act-left" onclick="UNote.moveTo(event)">'.self::ico('mv').'</a>';
	}
	public static function attActIcon ($id, $titl)
	{
		return '<a href="javascript:void(0);" title="'.$titl.'" class="act act-left" onclick="UNote.addAttach(event)">'.self::ico('aa').'</a>';
	}
	public static function delActIcon ($id, $titl)
	{
		return HTMLHelper::link(
				'javascript:UNote.deleteItem(event);',
			//	self::aiUrl('task=edit.deleteItem&iid='.$id),
				self::ico('dn','idang'),
				['title'=>$titl, 'class'=>'act act-right sure', 'data-suremsg'=>strtolower($titl)]
			);
	}
	public static function fNewActIcon ($id, $titl)
	{
		return HTMLHelper::link(
				'javascript:;',
				self::ico('nf'),
				['title'=>$titl, 'class'=>'act act-left', M34C::bs('toggle')=>'modal', M34C::bs('target')=>'#foldercr-modal']
			);
	}
	public static function fEdtActIcon ($id, $titl)
	{
		return HTMLHelper::link(
				'javascript:;',
				self::ico('ef'),
				['title'=>$titl, 'class'=>'act act-right', M34C::bs('toggle')=>'modal', M34C::bs('target')=>'#foldered-modal']
			);
	}
	public static function fDelActIcon ($id, $titl)
	{
		return HTMLHelper::link(
			//	self::aiUrl('task=edit.deleteItem&iid='.$id),
				'javascript:UNote.deleteItem(event);',
				self::ico('df','idang'),
				['title'=>$titl, 'class'=>'act act-right sure', 'data-suremsg'=>strtolower($titl)]
			);
	}
	public static function toolActIcon ($id, $titl)
	{
		return '<a href="javascript:void(0);" title="'.$titl.'" class="act act-left" onclick="UNote.toolMenu(event);">'.self::ico('to').'</a>';
	}


	public static function searchField ($pid, $val='')
	{
//		$mnuId = self::mnuId();
		$mnuId = self::$instanceObj->menuid;
		$fact = self::aiUrl('');
//		$sturl = str_replace(['+','/','='], ['-','_',''], base64_encode($string));
		return <<<EOD
<div class="search">
	<form name="sqry" action="{$fact}" method="POST" onsubmit="return UNote.performSearch(this)">
		<input type="hidden" name="task" value="search">
		<input type="hidden" name="pid" value="{$pid}">
		<input type="search" name="sterm" size="40" results="0" autosave="true" placeholder="Search..." value="{$val}">
		<input type="submit" style="display: none">
	</form>
</div>
EOD;
	}


	public static function nqMessage ($msg, $svrty)
	{
		Factory::getApplication()->enqueueMessage($msg, $svrty);
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
			$html .= '<div data-afile="'.rawurlencode($atch).'" class="atchlink">';
			$html .= '<a href="javascript:void(0);" class="noeffect" onclick="UNote.getAttach(event,this,true)" title="'.Text::_('COM_USERNOTES_DOWNFIL').'">'.self::ico('dl').'</a>';
			$html .= ' <a href="javascript:void(0);" class="noeffect" onclick="UNote.getAttach(event,this,false)" title="'.Text::_('COM_USERNOTES_VIEWFIL').'">'.$atch.'</a>';
			if ($edt) {
				$html .= ' <a href="javascript:void(0);" title="'.Text::_('COM_USERNOTES_DEL_ATT').'" class="att-left" onclick="UNote.aj_delAttach(event,'.$cid.',\''.$atch.'\')">'.self::ico('ax','idang').'</a>';
				$html .= ' <a href="javascript:void(0);" title="'.Text::_('COM_USERNOTES_REN_ATT').'" class="att-left" onclick="UNote.aj_renAttach(event,'.$cid.',\''.$atch.'\')">'.self::ico('ae','idarn').'</a>';
			}
			$html .= '</div>';
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
		$url = Route::_('index.php?option=com_usernotes'.($prms?('&'.$prms):'').'&Itemid='.self::$instanceObj->menuid, $xml);
		return $url;
	}


//	private static function mnuId ()
//	{
//		static $mnuId = 0;
//
//		if (!$mnuId) {
//			$mnuId = Factory::getApplication()->input->getInt('Itemid', 0);
//		}
//		return $mnuId;
//	}


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


	private static function ico ($ico, $clss='')
	{
		static $v;
		static $icos;
	
		if (!isset($v)) {
			$v = (int)JVERSION > 3 ? 1 : 0;
			$icos = [
				'nf'=>['icon-folder-plus-2','fa fa-folder-plus'],
				'ef'=>['icon-edit','fa fa-pencil-alt'],
				'df'=>['icon-folder-remove','fa fa-folder-minus'],
				'nn'=>['icon-file-plus','far fa-plus-square'],
				'en'=>['icon-edit','fa fa-edit'],
				'dn'=>['icon-file-minus','fa fa-minus'],
				'aa'=>['icon-attachment','fa fa-paperclip'],
				'ae'=>['icon-pencil-2','fa fa-pencil-alt'],
				'ax'=>['icon-remove','fa fa-times-circle'],
				'mv'=>['icon-move','fa fa-arrows-alt'],
				'to'=>['icon-wrench','fa fa-wrench'],
				'pr'=>['icon-print','fa fa-print'],
				'cm'=>['icon-comment','far fa-comment'],
				'cmm'=>['icon-comments-2','fas fa-comments'],
				'dl'=>['icon-download','fa fa-download']
			];
		}

		$icls = $icos[$ico][$v] . ($clss?(' '.$clss):'');
		return '<i class="'.$icls.'"> </i>';
		return '<i class="'.$ico.'" style="font-size:'.(IS_SMALL_DEVICE ? 28 : 16).'px"> </i>';
	}


}