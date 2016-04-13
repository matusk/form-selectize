<?php
/**
 * Copyright (c) 2014 Petr Olišar (http://olisar.eu)
 *
 * For the full copyright and license information, please view
 * the file LICENSE.md that was distributed with this source code.
 */

namespace App\Form\Control;

use Nette;
use Nette\Forms\Form,
	Nette\Utils\Html;

/**
 * Description of Selectize
 *
 * @author Petr Olišar <petr.olisar@gmail.com>
 */
class Selectize extends Nette\Forms\Controls\BaseControl
{
	private $entity;
	private $labelName;
	private $selectize;
	private $selectizeBack;
	private $options;
	private $prompt = FALSE;
	
	
	public function __construct($label = null, array $entity = NULL, array $config = NULL)
	{
		parent::__construct($label);
		$this->entity = is_null($entity) ? [] : $entity;
		$this->labelName = $label;
		$this->options = $config;
	}
	
	
	public function setOptions(array $options)
	{
		foreach($options as $key => $value)
		{
			$this->options[$key] = $value;
		}
		return $this;
	}
	
	
	public function setMode($mode)
	{
		$this->options['mode'] = $mode;
		return $this;
	}
	
	
	public function setCreate($create)
	{
		$this->options['create'] = $create;
		return $this;
	}
	
	
	public function maxItems($items)
	{
		$this->options['maxItems'] = $items;
		return $this;
	}
	
	
	public function setDelimiter($delimiter)
	{
		$this->options['delimiter'] = $delimiter;
		return $this;
	}
	
	
	public function setPlugins(array $plugins)
	{
		$this->options['plugins'] = $plugins;
		return $this;
	}
	
	
	public function setValueField($valueField)
	{
		$this->options['valueField'] = $valueField;
		return $this;
	}
	
	
	public function setLableField($lableField)
	{
		$this->options['lableField'] = $lableField;
		return $this;
	}
	
	
	public function setSearchField($searchField)
	{
		$this->options['searchField'] = $searchField;
		return $this;
	}
	
	
	public function setClass($class)
	{
		$this->options['class'] = $class;
		return $this;
	}
	
	
	/**
	 * Sets first prompt item in select box.
	 * @param  string
	 * @return self
	 */
	public function setPrompt($prompt)
	{
		$this->prompt = $prompt;
		return $this;
	}


	/**
	* Sets items in select box.
	* @param array $items
	* @return self
	*/
	public function setItems(array $items)
	{
		$this->entity = $items;
		return $this;
	}


	/**
	 * Returns first prompt item?
	 * @return mixed
	 */
	public function getPrompt()
	{
		return $this->prompt;
	}
	
	
	public function setValue($value)
	{
		if(!is_null($value))
		{
			if(is_array($value))
			{
				$i = 0;
				foreach($value as $slug)
				{
					$i++;
					$idName = $this->options['valueField'];
					$this->selectizeBack .= $slug->$idName;
					if($i < count($value))
					{
						$this->selectizeBack .= $this->options['delimiter'];
					}
				}
			} else
			{
				$this->selectizeBack = $value;
			}
		}
		
		$this->selectize = $this->selectizeBack;
	}
	
	
	public function getValue()
	{
		return count($this->selectize)
			? $this->selectize
			: NULL;
	}
	
	
	public function loadHttpData()
	{
		if($this->options['mode'] === 'select')
		{
			$this->selectizeBack = $this->selectize = $this->getHttpData(Form::DATA_LINE);
		} else
		{
			$this->prepareData();
		}
	}
	
	
	public function getControl()
	{
		$this->setOption('rendered', TRUE);
		$name = $this->getHtmlName();
		if($this->options['mode'] === 'full')
		{
			return Html::el('input', array(
				'type' => 'text',
				'name' => $name,
				'class' => array(isset($this->options['class']) ? $this->options['class'] : 'selectize' . ' form-control text'),
			))->data('entity', $this->entity)->data('options', $this->options)->value($this->selectizeBack);
		} elseif ($this->options['mode'] === 'select')
		{
			$this->entity = $this->prompt === FALSE ?
				$this->entity : self::arrayUnshiftAssoc($this->entity, '', $this->translate($this->prompt));
			return Nette\Forms\Helpers::createSelectBox($this->entity, [
					'selected?' => $this->selectizeBack
				])
				->name($name)
				->data('entity', $this->entity)
				->data('options', $this->options)
				->class(isset($this->options['class']) ? $this->options['class'] : 'selectize' . ' form-control');
		}
	}
	
	
	private static function arrayUnshiftAssoc(&$arr, $key, $val)
	{
		$arr = array_reverse($arr, true);
		$arr[$key] = $val;
		return array_reverse($arr, true);
	}
	
	
	private function prepareData()
	{
		$this->selectize = $this->split($this->getHttpData(Form::DATA_LINE));
		$this->selectizeBack = $this->getHttpData(Form::DATA_LINE);
		$iteration = false;
		foreach($this->selectize as $key => $value)
		{
			if(!$this->myInArray($this->entity, $value, $this->options['valueField']))
			{
				$iteration ?: $this->selectize['new'] = [];
				array_push($this->entity, [$this->options['valueField'] => $value, 'name' => $value]);
				array_push($this->selectize['new'], $value);
				unset($this->selectize[$key]);
				$iteration = true;
			}
		}
	}
	
	
	private function split($selectize)
	{
		$return = \Nette\Utils\Strings::split($selectize, '~'.$this->options['delimiter'].'\s*~');
		return $return[0] === "" ? [] : $return;
	}
	
	
	/**
	 * 
	 * @author <brouwer.p@gmail.com>
	 * @param array $array
	 * @param type $value
	 * @param type $key
	 * @return boolean
	 */
	private function myInArray(array $array, $value, $key)
	{
		foreach ($array as $val) 
		{
			if (is_array($val))
			{
				if($this->myInArray($val,$value,$key))
				return true;
			} else
			{
				if($array[$key]==$value)
				return true;
			}
		}
		return false;
	}
	
	
	public static function register($method = 'addSelectize', $config)
	{
		Nette\Forms\Container::extensionMethod($method, function(Nette\Forms\Container $container, $name, $label, $entity = null, array $options = null) use ($config)
		{
			$container[$name] = new Selectize($label, $entity, is_array($options) ?
				array_replace($config, $options) : $config);
			return $container[$name];
		});
	}
}
