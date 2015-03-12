<?php
/**
 * Project: coremodule
 * File: Panel.php
 * User: Panagiotis Vagenas <pan.vagenas@gmail.com>
 * Date: 15/11/2014
 * Time: 9:09 πμ
 * Since: 141110
 * Copyright: 2014 Panagiotis Vagenas
 */

namespace XDaRk_v150216\Panels;

use XDaRk_v150216\Core;

if ( ! defined( '_PS_VERSION_' ) ) {
	exit;
}


class Panel extends Core {
	protected $tab = 0;
	protected $type = 'main';
	protected $title = 'XDaRk_v150216 Core Options Panel';
	protected $image = false; // TODO set a default image
	protected $desc = null;
	protected $description = null;
	protected $input = array();
	protected $submit = array(
		'title' => 'Save',
		'class' => 'button pull-right'
	);
	protected $values = array();

	/**
	 * @param $label
	 * @param $name
	 * @param bool $required
	 * @param bool $hint
	 * @param string $class
	 * @param bool $description
	 *
	 * @param bool $prefix
	 * @param bool $suffix
	 * @param int $col
	 * @param string $type
	 *
	 * @return $this
	 *
	 * @author Panagiotis Vagenas <pan.vagenas@gmail.com>
	 * @since ${VERSION}
	 */
	public function addTextField( $label, $name, $required = true, $hint = false, $class = '', $description = false, $prefix = false, $suffix = false, $col = 6, $type = 'text' ) {
		$f = array(
			'type'     => $type,
			'label'    => $label,
			'name'     => $name,
			'class'    => $class,
			'required' => $required,
			'col'      => $col
		);

		if ( $hint !== false ) {
			$f['hint'] = $hint;
		}

		if ( $description !== false ) {
			$f['description'] = $description;
		}

		if ( $prefix !== false ) {
			$f['prefix'] = $prefix;
		}

		if ( $suffix !== false ) {
			$f['suffix'] = $suffix;
		}

		$this->addField( $f );

		return $this;
	}

	public function addHiddenField( $name ) {
		$f = array(
			'type' => 'hidden',
			'name' => $name,
		);
		$this->addField( $f );

		return $this;
	}

	public function addPasswordField( $label, $name, $class = '', $required = true, $hint = false, $description = false, $prefix = false, $suffix = false, $col = 6 ) {
		return $this->addTextField( $label, $name, $required, $hint, $class, $description, $prefix, $suffix, $col, 'password' );
	}

	public function addFileField( $label, $name, $class = '', $required = true, $hint = false, $description = false, $prefix = false, $suffix = false, $col = 6 ) {
		// TODO File options, check parent
		return $this->addTextField( $label, $name, $required, $hint, $class, $description, $prefix, $suffix, $col, 'datetime' );
	}

	public function addDateField( $label, $name, $class = '', $required = true, $hint = false, $description = false, $prefix = false, $suffix = false, $col = 6 ) {
		$class .= ' datepicker';

		return $this->addTextField( $label, $name, $required, $hint, $class, $description, $prefix, $suffix, $col, 'date' );
	}

	public function addDateTimeField( $label, $name, $class = '', $required = true, $hint = false, $description = false, $prefix = false, $suffix = false, $col = 6 ) {
		$class .= ' datepicker';

		return $this->addTextField( $label, $name, $required, $hint, $class, $description, $prefix, $suffix, $col, 'datetime' );
	}

	public function addTextAreaField( $label, $name, $class = '', $required = true, $hint = false, $description = false, $prefix = false, $suffix = false, $col = 6 ) {
		return $this->addTextField( $label, $name, $required, $hint, $class, $description, $prefix, $suffix, $col, 'textarea' );
	}

	public function addColorField( $label, $name, $class = '', $required = true, $hint = false, $description = false, $prefix = false, $suffix = false, $col = 6 ) {
		return $this->addTextField( $label, $name, $required, $hint, $class, $description, $prefix, $suffix, $col, 'color' );
	}

	/**
	 * @param $label
	 * @param $name
	 * @param string $class
	 * @param array $options
	 * @param bool $required
	 * @param bool $hint
	 * @param bool $description
	 * @param string $optionId
	 * @param string $optionName
	 *
	 * @param bool $prefix
	 * @param bool $suffix
	 *
	 * @return $this
	 *
	 * @author Panagiotis Vagenas <pan.vagenas@gmail.com>
	 * @since ${VERSION}
	 */
	public function addSelectField( $label, $name, Array $options, $required = true, $hint = false, $class = '', $description = false, $optionId = 'value', $optionName = 'name', $prefix = false, $suffix = false ) {
		$f = array(
			'type'     => 'select',
			'label'    => $label,
			'name'     => $name,
			'class'    => $class,
			'required' => $required,
			'options'  => array(
				'query' => $options,
				'id'    => $optionId,
				'name'  => $optionName
			)
		);
		if ( $hint !== false ) {
			$f['hint'] = $hint;
		}
		if ( $description !== false ) {
			$f['description'] = $description;
		}
		if ( $prefix !== false ) {
			$f['prefix'] = $prefix;
		}
		if ( $suffix !== false ) {
			$f['suffix'] = $suffix;
		}
		$this->addField( $f );

		return $this;
	}

	/**
	 * @param $label
	 * @param $name
	 * @param array $options
	 * @param bool $required
	 * @param bool $hint
	 * @param string $class
	 * @param bool $description
	 * @param string $optionValueName
	 * @param string $optionName
	 *
	 * @param bool $prefix
	 * @param bool $suffix
	 *
	 * @return $this
	 *
	 * @author Panagiotis Vagenas <pan.vagenas@gmail.com>
	 * @since ${VERSION}
	 */
	public function addMultiSelectField( $label, $name, Array $options, $required = false, $hint = false, $class = '', $description = false, $optionValueName = 'value', $optionName = 'name', $prefix = false, $suffix = false ) {
		$f = array(
			'type'     => 'select',
			'label'    => $label,
			'name'     => $name . '[]',
			'class'    => $class,
			'required' => $required,
			'multiple' => true,
			'options'  => array(
				'query' => $options,
				'id'    => $optionValueName,
				'name'  => $optionName
			)
		);
		if ( $hint !== false ) {
			$f['hint'] = $hint;
		}
		if ( $description !== false ) {
			$f['description'] = $description;
		}
		if ( $prefix !== false ) {
			$f['prefix'] = $prefix;
		}
		if ( $suffix !== false ) {
			$f['suffix'] = $suffix;
		}
		$this->addField( $f );

		return $this;
	}

	/**
	 * @param $label
	 * @param $name
	 * @param string $class
	 * @param array $values
	 * @param bool $required
	 * @param bool $isBool
	 * @param bool $hint
	 * @param bool $description
	 *
	 * @param bool $prefix
	 * @param bool $suffix
	 *
	 * @return $this
	 *
	 * @author Panagiotis Vagenas <pan.vagenas@gmail.com>
	 * @since ${VERSION}
	 */
	public function addSwitchField(
		$label,
		$name,
		Array $values,
		$required = true,
		$isBool = true,
		$hint = false,
		$class = '',
		$description = false,
		$prefix = false,
		$suffix = false
	) {
		$f = array(
			'type'     => 'switch',
			'label'    => $label,
			'name'     => $name,
			'class'    => $class,
			'required' => $required,
			'is_bool'  => $isBool,
			'values'   => $values
		);
		if ( $hint !== false ) {
			$f['hint'] = $hint;
		}

		if ( $description !== false ) {
			$f['description'] = $description;
		}

		if ( $prefix !== false ) {
			$f['prefix'] = $prefix;
		}

		if ( $suffix !== false ) {
			$f['suffix'] = $suffix;
		}

		$this->addField( $f );

		return $this;
	}

	public function addYesNoField(
		$label,
		$name,
		$required = true,
		$hint = false,
		$class = '',
		$description = false,
		$prefix = false,
		$suffix = false
	) {
		$yesNo = array(
			array(
				'id'    => $name . '_on',
				'value' => 1,
				'label' => $this->moduleInstance->l( 'Yes' )
			),
			array(
				'id'    => $name . '_off',
				'value' => 0,
				'label' => $this->moduleInstance->l( 'No' )
			)
		);

		return $this->addSwitchField( $label, $name, $yesNo, $required, true, $hint, $class, $description, $prefix, $suffix );
	}

	/**
	 * @param $html_content
	 * @param string $name
	 *
	 * @return $this
	 * @author Panagiotis Vagenas <pan.vagenas@gmail.com>
	 * @since 150216
	 */
	public function addHtml( $html_content, $name = '' ) {
		$f = array(
			'type'         => 'html',
			'name'         => $name,
			'html_content' => $html_content,
			'col'          => 12,
		);

		$this->addField( $f );

		return $this;
	}


	/**
	 * @param $field
	 *
	 * @return bool
	 *
	 * @author Panagiotis Vagenas <pan.vagenas@gmail.com>
	 * @since ${VERSION}
	 */
	protected function isMultiSelectField( $field ) {
		return is_array( $field ) && isset( $field['multiple'] ) && $field['multiple'] === true;
	}


	/**
	 * @param $field
	 *
	 * @return $this
	 *
	 * @author Panagiotis Vagenas <pan.vagenas@gmail.com>
	 * @since ${VERSION}
	 */
	public function addField( $field ) {
		if ( ! isset( $this->input ) || ! is_array( $this->input ) ) {
			$this->input = array();
		}
		array_push( $this->input, $field );

		return $this;
	}

	/**
	 * @param $index
	 * @param $title
	 * @param $image
	 * @param string $type
	 * @param string $submitTitle
	 * @param string $submitClass
	 *
	 * @return Panel
	 *
	 * @author Panagiotis Vagenas <pan.vagenas@gmail.com>
	 * @since ${VERSION}
	 */
	public function factory( $index, $title, $image, $type = 'main', $submitTitle = 'Save', $submitClass = 'button pull-right' ) {
		$panel = new self( $this->moduleInstance );
		$panel->setTab( $index );
		$panel->setTitle( $title );
		$this->title = $title;
		if ( $image ) {
			$panel->setImage( $image );
		}
		$panel->setSubmit( array(
			'title' => $submitTitle,
			'class' => $submitClass
		) );

		$panel->setType( $type );

		return $panel;
	}

	/**
	 *
	 * @return array
	 * @author Panagiotis Vagenas <pan.vagenas@gmail.com>
	 * @since 141110
	 */
	public function __toArray() {
		$ar = array();
		if ( ! empty( $this->title ) ) {
			$ar['form']['legend']['title'] = $this->title;
		}
		if ( ! empty( $this->image ) ) {
			$ar['form']['legend']['image'] = $this->image;
		}
		if ( ! empty( $this->desc ) ) {
			$ar['form']['desc'] = $this->desc;
		}
		if ( ! empty( $this->description ) ) {
			$ar['form']['description'] = $this->description;
		}
		if ( ! empty( $this->input ) ) {
			$ar['form']['input'] = $this->input;
		}
		if ( ! empty( $this->submit ) ) {
			$ar['form']['submit'] = $this->submit;
		}

		return $ar;
	}

	/**
	 * @param array $fieldValues
	 *
	 * @return array
	 * @author Panagiotis Vagenas <pan.vagenas@gmail.com>
	 * @since ${VERSION}
	 */
	public function parseFieldsValues( Array $fieldValues ) {
		$ar = array();
		foreach ( (array) $this->input as $ki => $fi ) {
			if ( $this->isMultiSelectField( $fi ) && isset( $fieldValues[ rtrim( $fi['name'], '[]' ) ] ) ) {
				$ar[ $fi['name'] ] = $fieldValues[ rtrim( $fi['name'], '[]' ) ];
			} elseif ( isset( $fieldValues[ $fi['name'] ] ) ) {
				$ar[ $fi['name'] ] = $fieldValues[ $fi['name'] ];
			}
		}

		return $ar;
	}

	public function isInSidebar() {
		return $this->type === 'sidebar';
	}

	/**
	 * @return boolean
	 */
	public function isImageSet() {
		return $this->image;
	}

	/**
	 * @param boolean $image
	 *
	 * @return $this
	 */
	public function setImage( $image ) {
		$this->image = $image;

		return $this;
	}

	/**
	 * @return array
	 */
	public function getInput() {
		return $this->input;
	}

	/**
	 * @param array $input
	 *
	 * @return $this
	 */
	public function setInput( $input ) {
		$this->input = $input;

		return $this;
	}

	/**
	 * @return int
	 */
	public function getTab() {
		return $this->tab;
	}

	/**
	 * @param int $tab
	 *
	 * @return $this
	 */
	public function setTab( $tab ) {
		$this->tab = $tab;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getTitle() {
		return $this->title;
	}

	/**
	 * @param string $title
	 *
	 * @return $this
	 */
	public function setTitle( $title ) {
		$this->title = $title;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getType() {
		return $this->type;
	}

	/**
	 * @param string $type
	 *
	 * @return $this
	 */
	public function setType( $type ) {
		$this->type = $type;

		return $this;
	}

	/**
	 * @param array $submit
	 *
	 * @return $this
	 */
	public function setSubmit( Array $submit ) {
		$this->submit = $submit;

		return $this;
	}

	public function getSubmit() {
		return $this->submit;
	}
} 