<?php
MailPress::require_class('Forms_field_type_abstract');

class MP_Forms_field_type_time extends MP_Forms_field_type_abstract
{
	var $field_type 		= 'time';
	var $order			= 71;
	var $field_not_input 	= true;

	function __construct()
	{
		$this->description = __('Time', MP_TXTDOM);
		$this->settings	 = dirname(__FILE__) . '/settings.xml';
		parent::__construct();
	}
	function get_name($field) { return $this->prefix.'['.$field->form_id . ']['. $field->id . ']' .  ( (isset($field->settings['options']['is'])) ? ( '[' . ( ($field->settings['options']['is'] == 'am_pm') ? $this->prefix . $field->settings['options']['is'] : $field->settings['options']['is'] ) . ']' ) : '' ) ; }
	function get_id($field)   { return $this->prefix  .  $field->form_id . '_' . $field->id .        ( (isset($field->settings['options']['is'])) ? ( '_' . ( ($field->settings['options']['is'] == 'am_pm') ? $field->settings['attributes']['value']           : $field->settings['options']['is'] )       ) : '' ) ; }
	public static function valid_date($y, $m, $d) { $feb = ((($y % 4 == 0) && ( (!($y % 100 == 0)) || ($y % 400 == 0))) ? 29 : 28 );  $maxd = array(31,$feb,31,30,31,30,31,31,30,31,30,31); if ($d > $maxd[$m - 1]) return false; return true; }

	function submitted($field)
	{
		if (isset($_POST[$this->prefix][$field->form_id][$field->id])) $value = $_POST[$this->prefix][$field->form_id][$field->id];

		$required 	= (isset($field->settings['controls']['required']) && $field->settings['controls']['required']);
		$empty 	= ( empty($value['h']) || empty($value['mn']) );

		if ($required && $empty)
		{
			$field->submitted['on_error'] = 1;
			return $field;
		}

		$format = $field->settings['options']['mail_time_format'];
		if (empty($format)) $format = get_option('time_format');

		$field->submitted['value'] = $value;
		$field->submitted['text']  = date($format, mktime($value['h'], $value['mn']));
		if (isset($value[$this->prefix . 'am_pm'])) 	$field->submitted['text'] .= ' '  . $value[$this->prefix . 'am_pm'];
		if (isset($value['tz'])) 				$field->submitted['text'] .= ' (' . $value['tz'] . ')'; 

		return $field;
	}

	function attributes_filter($no_reset)
	{
// hours
		$start	= -1;
		$max		= ('0' == $this->field->settings['options']['form_time_format']) ? 23 : 12;
   		$selectedh	= ('0' == $this->field->settings['options']['form_time_format']) ? date('H') : date('h');
		do { $start++; $k = $start; if ($k < 10) $k = '0' . $k; $v = $k; $list[$k] = $v; } while ($start < $max);
		$this->field->settings['options']['tag_content_h'] = MailPress::select_option($list, $selectedh, false);
// minutes
		$start	= -1;
		$max		= 59;
   		$selectedmn	= date('i');
		do { $start++; $k = $start; if ($k < 10) $k = '0' . $k; $v = $k; $list[$k] = $v; } while ($start < $max);
		$this->field->settings['options']['tag_content_mn'] = MailPress::select_option($list, $selectedmn, false);
// timezones
		if (isset($this->field->settings['options']['form_timezones'])) $this->field->settings['options']['tag_content_tz'] = file_get_contents(dirname(__FILE__) . '/timezones.xml');

		if (!$no_reset) return;
		
		$this->field->settings['options']['value'] = $_POST[$this->prefix][$this->field->form_id][$this->field->id];

		$html = MP_Forms_field_type_select::no_reset( $this->field->settings['options']['tag_content_h'], $this->field->settings['options']['value']['h'] );
		$this->field->settings['options']['tag_content_h'] = ($html) ? $html : '<!-- ' . htmlspecialchars( __('invalid select options', MP_TXTDOM) ) . ' -->';
		$html = MP_Forms_field_type_select::no_reset( $this->field->settings['options']['tag_content_mn'], $this->field->settings['options']['value']['mn'] );
		$this->field->settings['options']['tag_content_mn'] = ($html) ? $html : '<!-- ' . htmlspecialchars( __('invalid select options', MP_TXTDOM) ) . ' -->';
		if (isset($this->field->settings['options']['form_timezones']))
		{	
			$html = MP_Forms_field_type_select::no_reset( $this->field->settings['options']['tag_content_tz'], $this->field->settings['options']['value']['tz'] );
			$this->field->settings['options']['tag_content_tz'] = ($html) ? $html : '<!-- ' . htmlspecialchars( __('invalid select options', MP_TXTDOM) ) . ' -->';
		}

		$this->attributes_filter_css();
	}

	function build_tag()
	{
		$this->field->type = 'select';
// hours
		$this->field->settings['attributes']['tag_content'] = $this->field->settings['options']['tag_content_h'];
		$this->field->settings['options']['is'] = 'h';
		$id_h  = $this->get_id($this->field);
		$tag_h = parent::build_tag();
// minutes
		$this->field->settings['attributes']['tag_content'] = $this->field->settings['options']['tag_content_mn'];
		$this->field->settings['options']['is'] = 'mn';
		$id_mn  = $this->get_id($this->field);
		$tag_mn = parent::build_tag();

// timezones
		$id_tz  = $tag_tz = '';
		if (isset($this->field->settings['options']['form_timezones']))
		{
			$this->field->settings['attributes']['tag_content'] = $this->field->settings['options']['tag_content_tz'];
			$this->field->settings['options']['is'] = 'tz';
			$id_tz  = $this->get_id($this->field);
			$tag_tz = parent::build_tag();
		}

// am pm
		$tag_am = $id_am  = $text_am = $tag_pm = $id_pm  = $text_pm = '';
		if ('0' != $this->field->settings['options']['form_time_format'])
		{
			unset($this->field_not_input);

			$this->field->type = 'radio';
			$this->field->settings['attributes']['type']  = 'radio';
			$this->field->settings['attributes']['name']  = 'am_pm';
			$this->field->settings['options']['is'] = 'am_pm';

			$this->field->settings['attributes']['value'] = 'am';
			if (date('G') < 12) $this->field->settings['attributes']['checked'] = 'checked';
			$tag_am = parent::build_tag();
			$id_am  = $this->get_id($this->field);
			$text_am= __('am', MP_TXTDOM);

			$this->field->settings['attributes']['value'] = 'pm';
			if (date('G') >= 12) $this->field->settings['attributes']['checked'] = 'checked';
			$tag_pm = parent::build_tag();
			$id_pm  = $this->get_id($this->field);
			$text_pm= __('pm', MP_TXTDOM);

			$this->field_not_input = true;
		}

		$this->field->type = $this->field_type;

		$sf  = '';
		$sf  = ('0' != $this->field->settings['options']['form_time_format']) ? 'ampm' : '';
		$sf .= (isset($this->field->settings['options']['form_timezones']))   ? ( (empty($sf)) ? 'tz' : '_tz' ) : '';
		if (empty($sf)) $sf = 'alone';

		$form_formats['alone'] 		= '{{h}}&nbsp;:&nbsp;{{mn}}';
		$form_formats['ampm'] 		= '{{h}}&nbsp;:&nbsp;{{mn}}&nbsp;{{am}}&nbsp;<label id="{{id_am}}_label" for="{{id_am}}">{{text_am}}</label>&nbsp;{{pm}}&nbsp;<label id="{{id_pm}}_label" for="{{id_pm}}">{{text_pm}}</label>';
		$form_formats['tz'] 		= '{{h}}&nbsp;:&nbsp;{{mn}}&nbsp;{{tz}}';
		$form_formats['ampm_tz'] 	= '{{h}}&nbsp;:&nbsp;{{mn}}&nbsp;{{am}}&nbsp;<label id="{{id_am}}_label" for="{{id_am}}">{{text_am}}</label>&nbsp;{{pm}}&nbsp;<label id="{{id_pm}}_label" for="{{id_pm}}">{{text_pm}}</label>&nbsp;{{tz}}';

		MailPress::require_class('Forms');
		$form_template = MP_Forms::get_template($this->field->form_id);
		if ($form_template)
		{
			MailPress::require_class('Forms_templates');
			$form_templates = new MP_Forms_templates();
			$f = $form_templates->get_composite_template($form_template, $this->field_type);
			if (is_array($f)) $form_formats = array_merge($form_formats, $f);
		}

		$search[] = '{{h}}';		$replace[] = '%1$s';
		$search[] = '{{id_h}}'; 	$replace[] = '%2$s';
		$search[] = '{{mn}}'; 		$replace[] = '%3$s';
		$search[] = '{{id_mn}}';	$replace[] = '%4$s';

		$search[] = '{{am}}';		$replace[] = '%5$s';
		$search[] = '{{id_am}}';	$replace[] = '%6$s';
		$search[] = '{{text_am}}';	$replace[] = '%7$s';

		$search[] = '{{pm}}';		$replace[] = '%8$s';
		$search[] = '{{id_pm}}';	$replace[] = '%9$s';
		$search[] = '{{text_pm}}';	$replace[] = '%10$s';

		$search[] = '{{tz}}';		$replace[] = '%11$s';
		$search[] = '{{id_tz}}';	$replace[] = '%12$s';

		$html = str_replace($search, $replace, $form_formats[$sf] );
		return sprintf($html, $tag_h, $id_h, $tag_mn, $id_mn, $tag_am, $id_am, $text_am, $tag_pm, $id_pm, $text_pm, $tag_tz, $id_tz);
	}
}
$MP_Forms_field_type_time = new MP_Forms_field_type_time();
?>