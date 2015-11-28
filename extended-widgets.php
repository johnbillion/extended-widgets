<?php
/*

Copyright © 2012-2015 John Blackbourn

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

*/

abstract class Extended_Widget extends WP_Widget {

	public $args = null;
	public $instance = null;

	/**
	 * Constructor.
	 *
	 * @param string $id_base         Optional Base ID for the widget, lowercase and unique. If left empty,
	 *                                a portion of the widget's class name will be used Has to be unique.
	 * @param string $name            Name for the widget displayed on the configuration page.
	 * @param array  $widget_options  Optional. Widget options. See wp_register_sidebar_widget() for information
	 *                                on accepted arguments. Default empty array.
	 * @param array  $control_options Optional. Widget control options. See wp_register_widget_control() for
	 *                                information on accepted arguments. Default empty array.
	 */
	public function __construct( $id_base, $name, $widget_options = array(), $control_options = array() ) {
		parent::__construct( $id_base, $name, $widget_options, $control_options );
	}

	abstract protected function get_vars();

	protected function get_title() {
		if ( isset( $this->instance['title'] ) ) {
			return $this->instance['title'];
		}
		return false;
	}

	protected function get_template_name() {
		return '';
	}

	protected function has_wrapper() {
		return true;
	}

	final public function widget( $args, $instance ) {

		$this->args     = $args;
		$this->instance = $instance;

		$template_args = array(
			'dir'  => 'widgets',
			'vars' => array_merge( $this->args, $this->instance ),
		);

		if ( isset( $this->widget_options['cache'] ) ) {
			$template_args['cache'] = absint( $this->widget_options['cache'] );
		}

		if ( ! class_exists( 'Extended_Template_Part' ) ) {
			return;
		}

		$template = new Extended_Template_Part( $this->get_template_slug(), $this->get_template_name(), $template_args );

		if ( !$template->has_template() ) {
			return;
		}

		if ( $this->has_wrapper() ) {
			echo $this->args['before_widget'];
		}
		if ( $title = $this->get_title() ) {
			echo $this->args['before_title'] . esc_html( $title ) . $this->args['after_title'];
		}

		if ( false === $template->args['cache'] || ! $output = $template->get_cache() ) {
			$template->set_vars( $this->get_vars() );
			$output = $template->get_output();
		}

		echo $output;

		if ( $this->has_wrapper() ) {
			echo $this->args['after_widget'];
		}

	}

	final protected function get_template_slug() {

		# This method turns "Namespace_My_Special_Widget" into "my-special". Hence, every
		# widget that extends this class is expected to be namespaced.

		$base  = preg_replace( '/_widget$/i', '', $this->id_base ) ;
		$parts = explode( '_', $base );

		array_shift( $parts );

		return implode( '-', $parts );

	}

	public static function register() {
		register_widget( get_called_class() );
	}

}
