<?php
/**
 * @package    com_usernotes
 *
 * @copyright  Copyright (C) 2016 RJCreations - All rights reserved.
 * @license    GNU General Public License version 3 or later; see LICENSE.txt
 */
defined('JPATH_BASE') or die;

class JFormFieldGmkbValue extends JFormField
{
	protected $type = 'GmkbValue';

	protected function getInput()
	{
		$allowEdit		= ((string) $this->element['edit'] == 'true') ? true : false;
		$allowClear		= ((string) $this->element['clear'] != 'false') ? true : false;

		// Load language
		JFactory::getLanguage()->load('com_usernotes', JPATH_SITE);

		$cmpdef = $this->element['compdef'];
		$params = JComponentHelper::getParams('com_usernotes');
		$defsiz = $params->get($cmpdef);

		// create the component default display
		list($cdv,$cdm) = $this->num2gmkv($defsiz);
		$mc = array('KiB','MiB','GiB');
		$compdef = $cdv.$mc[$cdm];

		// class='required' for client side validation
		$class = '';

		if ($this->required)
		{
			$class = ' class="required modal-value"';
		}

		// turn the value into GMK and number
		list($uplsiz,$uplsizm) = $this->num2gmkv($this->value);

		// Setup variables for display.
		$html	= array();

		$html[] = '<input type="checkbox" id="'.$this->id.'_dchk" onclick="unotes_doDefault(this)" '.($this->value ? '' : 'checked ').'style="vertical-align:initial" />';
		$html[] = '<label for="'.$this->id.'_dchk" style="display:inline;margin-right:1em">'.JText::_('JDEFAULT').'</label>';

		$html[] = '<span class="input-gmkb'.($this->value ? '' : ' hidden').'">';
		$html[] = '<input type="text" class="input-medium" id="' . $this->id . '_name" value="' . $uplsiz .'" style="width:4em;text-align:right" />';
		$html[] = '<select id="' . $this->id . '_gmkb" onchange="unotes_doGmkb(this)" style="width:5em">';
		$html[] = '<option value="1024"'.($uplsizm==0?' selected="selected"':'').'>KiB</option>';
		$html[] = '<option value="1048576"'.($uplsizm==1?' selected="selected"':'').'>MiB</option>';
		$html[] = '<option value="1073741824"'.($uplsizm==2?' selected="selected"':'').'>GiB</option>';
		$html[] = '</select>';
		$html[] = '<input type="hidden" class="gmkb-valu" id="' . $this->id . '_id"' . $class . ' name="' . $this->name . '" value="' . $this->value . '" />';
		$html[] = '</span>';

		$html[] = '<span class="gmkb-dflt'.($this->value ? ' hidden' : '').'" style="opacity:0.5">'.$compdef.'</span>';

		static $scripted;
		if (!$scripted) {
			$scripted = true;
			$script = array();
			$script[] = 'function unotes_doDefault (elm) {';
			$script[] = '	if (elm.checked) {';
			$script[] = '		jQuery(elm).siblings(".input-gmkb").addClass("hidden");';
			$script[] = '		jQuery(elm).siblings(".gmkb-dflt").removeClass("hidden");';
			$script[] = '	} else {';
			$script[] = '		jQuery(elm).siblings(".gmkb-dflt").addClass("hidden");';
			$script[] = '		jQuery(elm).siblings(".input-gmkb").removeClass("hidden");';
			$script[] = '	}';
			$script[] = '}';
			$script[] = 'function unotes_doGmkb (elm) {';
			$script[] = '	var valu = jQuery(elm).siblings(".gmkb-valu").eq(0);console.log(valu);';
			$script[] = '	var numb = +jQuery(elm).siblings(".input-medium").val();console.log(numb);';
			$script[] = '	var shft = +jQuery(elm).val();console.log(shft);console.log(numb * shft);';
			$script[] = '	valu.val(numb * shft);';
			$script[] = '}';
			JFactory::getDocument()->addScriptDeclaration(implode("\n", $script)."\n");
		}
		return implode("\n", $html);
	}

	private function num2gmkv ($num)
	{
		$sizm = 0;
		if ($num) {
			if ($num % 1073741824 == 0) {
				$sizm = 2;
				$num = $num >> 30;
			} elseif ($num % 1048576 == 0) {
				$sizm = 1;
				$num = $num >> 20;
			} else {
				$num = $num >> 10;
			}
		} else {
			$num = '';
		}
		return array($num,$sizm);
	}

}
