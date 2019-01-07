<?php
namespace Tutor;

if ( ! defined( 'ABSPATH' ) )
	exit;

class Options {

	public $option;
	public $options_attr;

	public function __construct() {
		$this->option = (array) maybe_unserialize(get_option('tutor_option'));
		$this->options_attr = $this->options_attr();

		//Saving option
		add_action('wp_ajax_tutor_option_save', array($this, 'tutor_option_save'));
	}

	private function get($key = null, $default = false){
		$option = $this->option;
		if (empty($option) || ! is_array($option)){
			return $default;
		}
		if ( ! $key){
			return $option;
		}
		if (array_key_exists($key, $option)){
			return apply_filters($key, $option[$key]);
		}
		//Access array value via dot notation, such as option->get('value.subvalue')
		if (strpos($key, '.')){
			$option_key_array = explode('.', $key);
			$new_option = $option;
			foreach ($option_key_array as $dotKey){
				if (isset($new_option[$dotKey])){
					$new_option = $new_option[$dotKey];
				}else{
					return $default;
				}
			}
			return apply_filters($key, $new_option);
		}

		return $default;
	}

	public function tutor_option_save(){
		if ( ! isset($_POST['_wpnonce']) || ! wp_verify_nonce( $_POST['_wpnonce'], 'tutor_option_save' ) ){
			exit();
		}

		$option = (array) isset($_POST['tutor_option']) ? $_POST['tutor_option'] : array();
		$option = apply_filters('tutor_option_input', $option);
		update_option('tutor_option', $option);

		//re-sync settings
		init::tutor_activate();

		wp_send_json_success( array('msg' => __('Option Updated', 'tutor') ) );
	}
	
	public function options_attr(){
		$pages = tutor_utils()->get_pages();

		//$course_base = tutor_utils()->course_archive_page_url();
		$lesson_url = site_url().'/course/'.'sample-course/<code>lessons</code>/sample-lesson/';

		$student_url = tutor_utils()->profile_url();

		$attempts_allowed = array();
		$attempts_allowed['unlimited'] = __('Unlimited' , 'tutor');
		$attempts_allowed = array_merge($attempts_allowed, array_combine(range(1,20), range(1,20)));

		$attr = array(
			'general' => array(
				'label'     => __('General', 'tutor'),
				'sections'    => array(
					'general' => array(
						'label' => __('General', 'tutor'),
						'desc' => __('General Settings', 'tutor'),
						'fields' => array(
							'enable_public_profile' => array(
								'type'      => 'checkbox',
								'label'     => __('Enable Public Profile', 'tutor'),
								'default' => '0',
								'desc'      => __('Enable this to make a profile publicly visible',	'tutor')."<br />" .$student_url,
							),
							'load_tutor_css' => array(
								'type'      => 'checkbox',
								'label'     => __('Load Tutor default CSS', 'tutor'),
								'default'   => '1',
								'desc'      => __('If your theme has its own styling, then you can turn it off to load CSS from the plugin directory', 'tutor'),
							),
							'load_tutor_js' => array(
								'type'      => 'checkbox',
								'label'     => __('Load Tutor default JavaScript', 'tutor'),
								'default'   => '1',
								'desc'      => __('If you have put required script in your theme javascript file, then you can turn it off to load JavaScript from the plugin directory', 'tutor'),
							),
							'student_must_login_to_view_course' => array(
								'type'      => 'checkbox',
								'label'     => __('Course Permission', 'tutor'),
								'desc'      => __('Students must be logged in to view course', 'tutor'),
							),
							'delete_on_uninstall' => array(
								'type'      => 'checkbox',
								'label'     => __('Erase upon uninstallation', 'tutor'),
								'desc'      => __('Delete all data during uninstall', 'tutor'),
							),
						)
					)
				),
			),
			'course' => array(
				'label'     => __('Course', 'tutor'),
				'sections'    => array(
					'general' => array(
						'label' => __('General', 'tutor'),
						'desc' => __('Course Settings', 'tutor'),
						'fields' => array(
							'display_course_instructors' => array(
								'type'      => 'checkbox',
								'label'     => __('Display instructors profile', 'tutor'),
								'label_title'   => __('Show the instructor profile on course single page.', 'tutor'),
							),
							'enable_q_and_a_on_course' => array(
								'type'      => 'checkbox',
								'label'     => __('Enable Q &amp; A on course', 'tutor'),
								'default'   => '0',
								'desc'      => __('Allow student to place their questions and answers on the course page, only enrolled student can do this',	'tutor'),
							),
						),
					),
					'archive' => array(
						'label' => __('Archive', 'tutor'),
						'desc' => __('Course Archive Settings', 'tutor'),
						'fields' => array(
							'course_archive_page' => array(
								'type'      => 'select',
								'label'     => __('Course Archive Page', 'tutor'),
								'default'   => '0',
								'options'   => $pages,
								'desc'      => __('Choose the page from the dropdown list where you want to show all of the courses',	'tutor'),
							),
							'courses_col_per_row' => array(
								'type'      => 'slider',
								'label'     => __('Column per row', 'tutor'),
								'default'   => '4',
								'options'   => array('min'=> 1, 'max' => 6),
								'desc'      => __('Define how many column you want to show on the course single page', 'tutor'),
							),
							'courses_per_page' => array(
								'type'      => 'slider',
								'label'     => __('Courses Per Page', 'tutor'),
								'default'   => '12',
								'options'   => array('min'=> 1, 'max' => 20),
								'desc'      => __('Define how many courses you want to show per page', 'tutor'),
							),
						),
					),
				),
			),
			'lesson' => array(
				'label' => __('Lessons', 'tutor'),
				'sections'    => array(
					'lesson_settings' => array(
						'label' => __('Lesson Settings', 'tutor'),
						'desc' => __('Lesson settings will be here', 'tutor'),
						'fields' => array(
							'lesson_permalink_base' => array(
								'type'      => 'text',
								'label'     => __('Lesson Permalink Base', 'tutor'),
								'default'   => 'lessons',
								'desc'      => $lesson_url,
							),

						),

					),

				),
			),
			'quiz' => array(
				'label' => __('Quiz', 'tutor'),
				'sections'    => array(
					'general' => array(
						'label' => __('Quiz', 'tutor'),
						'desc' => __('The values you set here define the default values that are used in the settings form when you create a new quiz.', 'tutor'),
						'fields' => array(
							'quiz_time_limit' => array(
								'type'      => 'group_fields',
								'label'     => __('Time Limit', 'tutor'),
								'desc'      => __('Default time limit for quizzes. 0 means no time limit.', 'tutor'),
								'group_fields'  => array(
									'value' => array(
										'type'      => 'text',
										'default'   => '0',
									),
									'time' => array(
										'type'      => 'select',
										'default'   => 'minutes',
										'select_options'   => false,
										'options'   => array(
											'weeks'     =>  __('Weeks', 'tutor'),
											'days'      =>  __('Days', 'tutor'),
											'hours'     =>  __('Hours', 'tutor'),
											'minutes'   =>  __('Minutes', 'tutor'),
											'seconds'   =>  __('Seconds', 'tutor'),
										),
									),
								),
							),

							'quiz_when_time_expires' => array(
								'type'      => 'select',
								'label'      => __('When time expires', 'tutor'),
								'default'   => 'minutes',
								'select_options'   => false,
								'options'   => array(
									'autosubmit'    =>  __('Current attempts are submitted automatically', 'tutor'),
									'graceperiod'   =>  __('There is a grace period when current attempts can be submitted, but no more questions answered', 'tutor'),
									'autoabandon'   =>  __('Attempts must be submitted before time expires, otherwise they will not be counted', 'tutor'),
								),
								'desc'  => __('What should happen by default if a student does not submit the quiz before time expires.', 'tutor'),
							),

							'quiz_attempts_allowed' => array(
								'type'      => 'slider',
								'label'      => __('Attempts allowed', 'tutor'),
								'default'   => '10',
								'options'   => array('min'=> 0, 'max' => 20),
								'desc'  => __('Restriction on the number of attempts students are allowed to take for a quiz. 0 for no limit', 'tutor'),
							),

							'quiz_grade_method' => array(
								'type'      => 'select',
								'label'      => __('Grading method', 'tutor'),
								'default'   => 'minutes',
								'select_options'   => false,
								'options'   => array(
									'highest_grade' => __('Highest Grade', 'tutor'),
									'average_grade' => __('Average Grade', 'tutor'),
									'first_attempt' => __('First Attempt', 'tutor'),
									'last_attempt' => __('Last Attempt', 'tutor'),
								),
								'desc'  => __('When multiple attempts are allowed, which method should be used to calculate a student\'s final grade for the quiz.', 'tutor'),
							),
						)
					)
				),
			),
			'instructors' => array(
				'label'     => __('Instructors', 'tutor'),
				'sections'    => array(
					'general' => array(
						'label' => __('Instructor Profile Settings', 'tutor'),
						'desc' => __('Enable Disable Option to on/off notification on various event', 'tutor'),
						'fields' => array(
							'instructor_register_page' => array(
								'type'      => 'select',
								'label'     => __('Instructor Register Page', 'tutor'),
								'default'   => '0',
								'options'   => $pages,
								'desc'      => __('This will be instructor register page', 'tutor'),
							),
							'instructor_can_publish_course' => array(
								'type'      => 'checkbox',
								'label'     => __('Can publish course', 'tutor'),
								'default' => '0',
								'desc'      => __('Define if a instructor can publish his courses directly or not, if unchecked, they can still add courses, but it will go to admin for review',	'tutor'),
							),
						),
					),
				),
			),

			'students' => array(
				'label'     => __('Students', 'tutor'),
				'sections'    => array(
					'general' => array(
						'label' => __('Student Profile settings', 'tutor'),
						'desc' => __('Enable Disable Option to on/off notification on various event', 'tutor'),
						'fields' => array(
							'student_register_page' => array(
								'type'      => 'select',
								'label'     => __('Student Register Page', 'tutor'),
								'default'   => '0',
								'options'   => $pages,
								'desc'      => __('Choose the page for student registration page', 'tutor'),
							),
							'student_dashboard' => array(
								'type'      => 'select',
								'label'     => __('Student Dashboard', 'tutor'),
								'default'   => '0',
								'options'   => $pages,
								'desc'      => __('This page will show students related stuff, like my courses, order, etc', 'tutor'),
							),

							'students_own_review_show_at_profile' => array(
								'type'      => 'checkbox',
								'label'     => __('Show reviews on profile', 'tutor'),
								'label_title'     => __('Enable students review on their profile', 'tutor'),
								'default' => '0',
								'desc'      => __('Enabling this will allow the reviews written by each individual students on their profile',	'tutor')."<br />" .$student_url,
							),
							'show_courses_completed_by_student' => array(
								'type'      => 'checkbox',
								'label'     => __('Show Completed Course', 'tutor'),
								'default' => '0',
								'desc'      => __('Completed courses will be show on student profile',	'tutor')."<br />".$student_url,
							),

						),
					),
				),
			),


		);

		return apply_filters('tutor/options/attr', $attr);
	}


	/**
	 * @param array $field
	 *
	 * @return string
	 *
	 * Generate Option Field
	 */
	public function generate_field($field = array()){
		ob_start();
		include tutor()->path.'views/options/option_field.php';
		return ob_get_clean();
	}

	public function field_type($field = array()){
		ob_start();
		include tutor()->path."views/options/field-types/{$field['type']}.php";
		return ob_get_clean();
	}

	public function generate(){
		ob_start();
		include tutor()->path.'views/options/options_generator.php';
		return ob_get_clean();
	}



}