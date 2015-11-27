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

abstract class Amsterdam_Widget extends WP_Widget {

	public $args = null;
	public $instance = null;

	public function __construct() {
		call_user_func_array( 'parent::__construct', func_get_args() );
	}

	abstract public function process();

	public function title() {
		if ( isset( $this->instance['title'] ) )
			return $this->instance['title'];
		return false;
	}

	public function wrapper() {
		return true;
	}

	public function widget( $args, $instance ) {

		$this->args     = $args;
		$this->instance = $instance;

		$template_args = array(
			'dir'  => 'widgets',
			'vars' => array_merge( $this->args, $this->instance )
		);

		if ( isset( $this->args['cache_timeout'] ) )
			$template_args['cache_timeout'] = $this->args['cache_timeout'];

		$template = new Amsterdam_Section_Template( $this->get_template_name(), null, $template_args );

		if ( !$template->has_template() )
			return;

		if ( $this->wrapper() )
			echo $this->args['before_widget'];
		if ( $title = $this->title() )
			echo $this->args['before_title'] . esc_html( $title ) . $this->args['after_title'];

		if ( isset( $this->args['cache'] ) and $this->args['cache'] ) {

			# @TODO check this caching code
			if ( !$output = $template->get_cache() ) {
				$template->set_vars( $this->process() );
				$output = $template->get_template();
			}

			echo $output;

		} else {

			$template->set_vars( $this->process() );
			$template->output_template();

		}

		if ( !isset( $this->args['wrapper'] ) or $this->args['wrapper'] )
			echo $this->args['after_widget'];

	}

	public function get_template_name() {

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
