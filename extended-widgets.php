<?php
/*

Copyright © 2012-2016 John Blackbourn

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

	/**
	 * Display arguments for this widget.
	 *
	 * @var array
	 */
	public $args = [];

	/**
	 * The settings for the particular instance of the widget.
	 *
	 * @var array
	 */
	public $instance = [];

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
	public function __construct( $id_base, $name, array $widget_options = [], array $control_options = [] ) {
		parent::__construct( $id_base, $name, $widget_options, $control_options );
	}

	/**
	 * Return the variables for use in this widget's template output.
	 *
	 * @return array Associative array of template variables.
	 */
	abstract protected function get_vars();

	/**
	 * Return the title for this widget based on the `title` instance variable.
	 *
	 * @return string|bool Title as a string, or boolean false if there isn't one.
	 */
	protected function get_title() {
		if ( isset( $this->instance['title'] ) ) {
			return $this->instance['title'];
		}
		return false;
	}

	/**
	 * Return the template name for this widget.
	 *
	 * The template name is optionally used when locating the widget's specialised template. It's functionally
	 * identical to the `$name` parameter of `get_template_part()` and can be used to add specificity to a template.
	 *
	 * @return string The name of the specialised template. Empty string when not used.
	 */
	protected function get_template_name() {
		return '';
	}

	/**
	 * Whether this widget's output should have a wrapper. Controls whether the `before_widget` and `after_widget`
	 * arguments are output around the widget.
	 *
	 * @return bool Should this widget's output have a wrapper?
	 */
	protected function has_wrapper() {
		return true;
	}

	/**
	 * Output the widget.
	 *
	 * This method controls locating the template, handling caching, and outputting the widget's contents.
	 *
	 * @param array $args     Display arguments including 'before_title', 'after_title',
	 *                        'before_widget', and 'after_widget'.
	 * @param array $instance The settings for the particular instance of the widget.
	 */
	final public function widget( $args, $instance ) {

		if ( ! is_array( $args ) ) {
			$args = [];
		}
		if ( ! is_array( $instance ) ) {
			$instance = [];
		}

		$this->args     = $args;
		$this->instance = $instance;

		$template_args = [
			'dir'  => 'widgets',
		];

		if ( isset( $this->widget_options['cache'] ) ) {
			$template_args['cache'] = absint( $this->widget_options['cache'] );
		}

		if ( ! class_exists( 'Extended_Template_Part' ) ) {
			return;
		}

		$template_vars = array_merge( $this->args, $this->instance );
		$template = new Extended_Template_Part( $this->get_template_slug(), $this->get_template_name(), $template_vars, $template_args );

		if ( ! $template->has_template() ) {
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

	/**
	 * Return the template slug for this widget.
	 *
	 * The template slug is used when locating the widget's generic template. It's functionally identical to the
	 * `$slug` parameter of `get_template_part()`.
	 *
	 * By default the slug is generated from the widget name but can be overridden by a child class.
	 *
	 * @return string The slug name for the generic template.
	 */
	protected function get_template_slug() {

		# This method turns "Prefix_My_Special_Widget" into "my-special". Hence, every
		# widget that extends this class is expected to be prefixed.
		# @TODO Support for namespaces

		$base  = preg_replace( '/_widget$/i', '', $this->id_base ) ;
		$parts = explode( '_', $base );

		array_shift( $parts );

		return implode( '-', $parts );

	}

	final public static function register() {
		register_widget( get_called_class() );
	}

}
