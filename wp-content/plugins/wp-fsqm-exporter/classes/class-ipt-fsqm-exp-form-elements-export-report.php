<?php
require_once IPT_FSQM_EXP_Loader::$abs_path . '/lib/phpExcel/PHPExcel.php';
/**
 * IPT FSQM Export Report
 *
 * @package IPT FSQM Export
 * @subpackage Form Elements
 * @author Swashata Ghosh <swashata@intechgrity.com>
 */
class IPT_FSQM_EXP_Form_Elements_Export_Report extends IPT_FSQM_Form_Elements_Base {
	public $exp_id = null;
	public $exp_data = null;

	public $type = null;
	public $formatStyles = array();
	public $objPHPExcel;
	public $objWriter;
	public $activeSheetIndex = 0;

	public $surveyIterator = 0;
	public $feedbackIterator = 0;
	public $activeSheetRowIterator = 1;

	public $path = null;

	public $uniqid = null;
	public $uniqpath = null;
	public $graphpath = null;

	public $preset_types = array();

	public $timer = null;

	const DIMENSION_CHART_X = 60;
	const DIMENSION_CHART_Y = 20;

	public function __construct( $exp_id ) {
		global $wpdb, $ipt_fsqm_exp_info;
		$row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$ipt_fsqm_exp_info['exp_table']} WHERE id = %d", $exp_id ) );
		if ( null == $row ) {
			return;
		}
		$this->exp_data = $row;

		$this->exp_data->survey = maybe_unserialize( $row->survey );
		$this->exp_data->feedback = maybe_unserialize( $row->feedback );

		$this->exp_id = $exp_id;
		$this->preset_types = array( 'xlsx', 'pdf', 'xls', 'html' );
		$wp_upload_dir = wp_upload_dir();
		$this->path = $wp_upload_dir['basedir'] . '/fsqm-exp-reports';

		if ( !wp_mkdir_p( $this->path ) ) {
			wp_die( __( 'Can not create proper directory. Please check your upload directory has the right permissions.', 'ipt_fsqm_exp' ) );
		}

		if ( !file_exists( $this->path . '/.htaccess' ) ) {
			file_put_contents( $this->path . '/.htaccess', "deny from all" );
		}

		$this->formatStyles = array(
			'title' => array(
				'font' => array(
					'bold' => true,
					'size' => 16,
				),
			),
			'subtitle' => array(
				'font' => array(
					'italic' => true,
					'size' => 12,
				),
			),
			'data_head' => array(
				'font' => array(
					'bold' => true,
					'alignment' => array(
						'hortizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
						'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
					),
				),
			),
		);

		if ( !PHPExcel_Settings::setChartRenderer(
				PHPExcel_Settings::CHART_RENDERER_JPGRAPH,
				IPT_FSQM_EXP_Loader::$abs_path . '/lib/jpgraph3.5.0b1'
			) ) {
			wp_die( __( 'Could not load jpgraph library', 'ipt_fsqm_exp' ) );
		}

		parent::__construct( $row->form_id );
	}

	/*==========================================================================
	 * Main APIs
	 *========================================================================*/
	public function stream_export( $type = 'xlsx' ) {
		if ( null == $this->exp_id ) {
			wp_die( __( 'Cheatin&#8217; uh?' ) );
		}

		if ( !in_array( $type, $this->preset_types ) ) {
			$type = 'xlsx';
		}

		$this->type = $type;

		$this->timer = microtime( true );

		$this->uniqid = 'ipt-fsqm-exp-' . $this->exp_id . '.' . $type;
		$this->uniqpath = $this->path . '/' . $this->uniqid;
		$file = $this->uniqpath . '.zip';

		if ( !file_exists( $file ) ) {
			//Check to see if directory exists, just in case
			if ( file_exists( $this->uniqpath ) ) {
				if ( is_dir( $this->uniqpath ) ) {
					$this->delTree( $this->uniqpath );
				} else {
					@unlink( $this->uniqpath );
				}
			}

			$this->create_report( $type );
			$this->zip( $this->uniqpath, $file );
		}

		//Set the debug information
		$debug_arr = array(
			array( __( 'Peak Memory Usage', 'ipt_fsqm_exp' ), ( memory_get_peak_usage( true ) / 1024 / 1024 ) . 'MB' ),
			array( __( 'Total Memory Usage', 'ipt_fsqm_exp' ), ( memory_get_usage( true ) / 1024 / 1024 ) . 'MB' ),
			array( __( 'File Written in', 'ipt_fsqm_exp' ), $this->uniqpath ),
			array( __( 'Execution Time', 'ipt_fsqm_exp' ), number_format( ( microtime( true ) - $this->timer ) ) . ' Seconds' )
		);
		ob_start();
		print_r( $debug_arr );
		$debug_info = ob_get_clean();

		$debug_file = $this->uniqpath . '/debug.txt';
		if ( file_exists( $debug_file ) ) {
			@unlink( $debug_file );
		}
		file_put_contents( $debug_file, $debug_info );

		header( 'Content-Description: File Transfer' );
		header( 'Content-Type: application/octet-stream' );
		header( 'Content-Disposition: attachment; filename='.basename( $file ) );
		header( 'Content-Transfer-Encoding: binary' );
		header( 'Expires: 0' );
		header( 'Cache-Control: must-revalidate' );
		header( 'Pragma: public' );
		header( 'Content-Length: ' . filesize( $file ) );
		@ob_clean();
		readfile( $file );
		exit;
	}

	public function create_report( $type = 'xlsx' ) {
		global $ipt_fsqm_exp_settings;
		if ( !wp_mkdir_p( $this->uniqpath ) ) {
			wp_die( __( 'Can not create proper directory. Please check your upload directory has the right permissions.', 'ipt_fsqm_exp' ) );
		}
		if ( !wp_mkdir_p( $this->uniqpath . '/graphs' ) ) {
			wp_die( __( 'Can not create proper directory. Please check your upload directory has the right permissions.', 'ipt_fsqm_exp' ) );
		}

		//Do some resource expensive sacrifice
		IPT_FSQM_EXP_Export_API::extend_resources( true );

		$this->graphpath = $this->uniqpath . '/graphs';
		$this->objPHPExcel = new PHPExcel();
		$this->objPHPExcel->getProperties()->setCreator( __( 'Exporter for FSQM Pro', 'ipt_fsqm_exp' ) )
		->setLastModifiedBy( __( 'Exporter for FSQM Pro', 'ipt_fsqm_exp' ) )
		->setTitle( $this->name )
		->setSubject( sprintf( __( 'Report generated on %s | %s', 'ipt_fsqm_exp' ), date_i18n( get_option( 'date_format' ) . __( '\,\ \a\t ', 'ipt_fsqm_exp' ) . get_option( 'time_format' ), strtotime( $this->exp_data->created ) ), get_bloginfo( 'name' ) ) )
		->setCategory( __( 'FSQM Report', 'ipt_fsqm_exp' ) );

		//Do some calculations here
		//mcqs/surveys
		foreach ( $this->mcq as $m_key => $mcq ) {
			if ( ! isset( $this->exp_data->survey[$m_key] ) ) {
				continue;
			}
			$mcq['title'] = strip_tags( $mcq['title'] );
			$mcq['subtitle'] = strip_tags( $mcq['subtitle'] );
			if ( method_exists( $this, 'report_' . $mcq['type'] ) && isset( $this->exp_data->survey[$m_key] ) && is_array( $this->exp_data->survey[$m_key] ) ) {
				$this->create_worksheet( sprintf( __( 'Survey%d', 'ipt_fsqm_exp' ), $this->surveyIterator++, array( $mcq['title'], $mcq['subtitle'] ) ) );
				call_user_func_array( array( $this, 'report_' . $mcq['type'] ), array( $mcq, $this->exp_data->survey[$m_key] ) );
			} else {
				$definition = $this->get_element_definition( $mcq );
				//Support for valid callbacks
				if ( isset( $definition['report_export_callback'] ) && is_callable( $definition['report_export_callback'], true ) ) {
					call_user_func_array( $definition['report_export_callback'], array( $mcq, $this->exp_data->survey[$m_key], $this ) );
				}
			}
		}
		//freetypes/feedbacks
		foreach ( $this->freetype as $f_key => $freetype ) {
			if ( ! isset( $this->exp_data->feedback[$f_key] ) ) {
				continue;
			}
			$freetype['title'] = strip_tags( $freetype['title'] );
			$freetype['subtitle'] = strip_tags( $freetype['subtitle'] );
			if ( method_exists( $this, 'report_' . $freetype['type'] ) && isset( $this->exp_data->feedback[$f_key] ) && is_array( $this->exp_data->feedback[$f_key] ) ) {
				$this->create_worksheet( sprintf( __( 'Feedback%d', 'ipt_fsqm_exp' ), $this->feedbackIterator++, array( $freetype['title'], $freetype['subtitle'] ) ) );
				call_user_func_array( array( $this, 'report_' . $freetype['type'] ), array( $freetype, $this->exp_data->feedback[$f_key] ) );
			} else {
				$definition = $this->get_element_definition( $mcq );
				//Support for valid callbacks
				if ( isset( $definition['report_export_callback'] ) && is_callable( $definition['report_export_callback'], true ) ) {
					call_user_func_array( $definition['report_export_callback'], array( $freetype, $this->exp_data->feedback[$f_key], $this ) );
				}
			}
		}

		//Set the filename
		$filename = $this->uniqpath . '/report' . '.' . $type;

		//Set the writer
		switch ( $type ) {
		case 'xlsx' :
			$this->objWriter = PHPExcel_IOFactory::createWriter( $this->objPHPExcel, 'Excel2007' );
			$this->objWriter->setIncludeCharts( TRUE );
			break;
		case 'pdf' :
			if ( !PHPExcel_Settings::setPdfRenderer(
					PHPExcel_Settings::PDF_RENDERER_MPDF,
					IPT_FSQM_EXP_Loader::$abs_path . '/lib/mPDF5.7.1'
				) ) {
				wp_die( __( 'Could not load mPDF library', 'ipt_fsqm_exp' ) );
			}
			$this->objWriter = PHPExcel_IOFactory::createWriter( $this->objPHPExcel, 'PDF' );
			$this->objWriter->writeAllSheets();
			$this->objWriter->setImagesRoot( '' );
			$this->objWriter->setHTMLHeader( $ipt_fsqm_exp_settings['html_header'] );
			$this->objWriter->setHTMLFooter( $ipt_fsqm_exp_settings['html_footer'] );
			$this->objWriter->setAdditionalCSS( $ipt_fsqm_exp_settings['style'] );
			break;
		case 'xls' :
			$this->objWriter = PHPExcel_IOFactory::createWriter( $this->objPHPExcel, 'Excel5' );
			break;
		case 'html' :
			$this->objWriter = PHPExcel_IOFactory::createWriter( $this->objPHPExcel, 'HTML' );
			$this->objWriter->setImagesRoot( 'graphs/' );
			$this->objWriter->setGenerateSheetNavigationBlock( false );
			$this->objWriter->writeAllSheets();
			$this->objWriter->setHTMLHeader( $ipt_fsqm_exp_settings['html_header'] );
			$this->objWriter->setHTMLFooter( $ipt_fsqm_exp_settings['html_footer'] );
			$this->objWriter->setAdditionalCSS( $ipt_fsqm_exp_settings['style'] );
			break;
		}

		//Save it
		$this->objPHPExcel->setActiveSheetIndex( 0 );
		$this->objWriter->save( $filename );
	}


	/*==========================================================================
	 * Individual method for the element types
	 *========================================================================*/
	public function report_radio( $mcq, $exp_data ) {
		$this->report_make_mcqs( $mcq, $exp_data );
	}

	public function report_checkbox( $mcq, $exp_data ) {
		$this->report_make_mcqs( $mcq, $exp_data );
	}

	public function report_select( $mcq, $exp_data ) {
		$this->report_make_mcqs( $mcq, $exp_data );
	}

	public function report_slider( $mcq, $exp_data ) {
		$this->report_make_slider( 1, $mcq['title'], $mcq['subtitle'], $exp_data, $mcq['settings'], array( '#', __( 'Value', 'ipt_fsqm_exp' ), __( 'Count', 'ipt_fsqm_exp' ), __( 'Percent', 'ipt_fsqm_exp' ) ) );
	}

	public function report_range( $mcq, $exp_data ) {
		$this->report_make_range( 1, $mcq['title'], $mcq['subtitle'], $exp_data, $mcq['settings'], array( '#', __( 'Count', 'ipt_fsqm_exp' ), __( 'To', 'ipt_fsqm_exp' ), __( 'From', 'ipt_fsqm_exp' ), __( 'Min', 'ipt_fsqm_exp' ), __( 'Max', 'ipt_fsqm_exp' ), __( 'Percent', 'ipt_fsqm_exp' ) ) );
	}

	public function report_spinners( $mcq, $exp_data ) {
		$this->report_make_spinners_ratings( $mcq, $exp_data );
	}

	public function report_grading( $mcq, $exp_data ) {
		$activeSheet = $this->objPHPExcel->getActiveSheet();
		$is_subtitle = false;
		$data_array = array();
		$data_array[] = array( $mcq['title'], '', '' );
		if ( trim( $mcq['subtitle'] ) != '' ) {
			$data_array[] = array( $mcq['subtitle'], '', '' );
			$is_subtitle = true;
		}
		$activeSheet->fromArray( $data_array, null, 'A1', true );

		$this->set_row_iterator( count( $data_array ) );

		$activeSheet->getStyle( 'A1' )->applyFromArray( $this->formatStyles['title'] );
		if ( $mcq['settings']['range'] == true ) {
			$activeSheet->mergeCells( 'A1:H1' );
		} else {
			$activeSheet->mergeCells( 'A1:E1' );
		}


		if ( $is_subtitle ) {
			if ( $mcq['settings']['range'] == true ) {
				$activeSheet->mergeCells( 'A2:H2' );
			} else {
				$activeSheet->mergeCells( 'A2:E2' );
			}
			$activeSheet->getStyle( 'A2' )->applyFromArray( $this->formatStyles['subtitle'] );
		}

		foreach ( $mcq['settings']['options'] as $o_key => $option ) {
			if ( !isset( $exp_data["$o_key"] ) ) {
				$exp_data["$o_key"] = array();
			}
			if ( $mcq['settings']['range'] == true ) {
				$this->report_make_range( $this->activeSheetRowIterator, $option, '', $exp_data["$o_key"], $mcq['settings'], array( '#', __( 'Count', 'ipt_fsqm_exp' ), __( 'To', 'ipt_fsqm_exp' ), __( 'From', 'ipt_fsqm_exp' ), __( 'Min', 'ipt_fsqm_exp' ), __( 'Max', 'ipt_fsqm_exp' ), __( 'Percent', 'ipt_fsqm_exp' ) ), $o_key );
			} else {
				$this->report_make_slider( $this->activeSheetRowIterator, $option, '', $exp_data["$o_key"], $mcq['settings'], array( '#', __( 'Value', 'ipt_fsqm_exp' ), __( 'Count', 'ipt_fsqm_exp' ), __( 'Percent', 'ipt_fsqm_exp' ) ), $o_key );
			}
		}
	}

	public function report_starrating( $mcq, $exp_data ) {
		$this->report_make_spinners_ratings( $mcq, $exp_data );
	}

	public function report_scalerating( $mcq, $exp_data ) {
		$this->report_make_spinners_ratings( $mcq, $exp_data );
	}

	public function report_matrix( $mcq, $exp_data ) {
		$activeSheet = $this->objPHPExcel->getActiveSheet();
		$is_subtitle = false;
		$data_array = array();
		$data_array[] = array( $mcq['title'] );
		if ( trim( $mcq['subtitle'] ) != '' ) {
			$data_array[] = array( $mcq['subtitle'] );
			$is_subtitle = true;
		}

		//Prep the head array
		$head_array = array();
		$head_array[] = '';

		foreach ( $mcq['settings']['columns'] as $column ) {
			$head_array[] = strip_tags( $column );
		}

		$data_array[] = $head_array;

		//Insert the row values
		foreach ( $mcq['settings']['rows'] as $r_key => $row ) {
			$tmp_arr = array();
			$tmp_arr[] = strip_tags( $row );

			foreach ( $mcq['settings']['columns'] as $c_key => $column ) {
				if ( ! isset( $exp_data["$r_key"] ) ) {
					$exp_data["$r_key"] = array();
				}
				$tmp_arr[] = isset( $exp_data["$r_key"]["$c_key"] ) ? (int) $exp_data["$r_key"]["$c_key"] : 0;
			}
			$data_array[] = $tmp_arr;
		}

		//Prep the end column and data cell offset
		$data_end_col = PHPExcel_Cell::stringFromColumnIndex( count( $head_array ) - 1 );
		$data_cell_offset = $is_subtitle ? 4 : 3;

		//Prep the average array
		$tmp_arr = array();
		$tmp_arr[] = __( 'Average', 'ipt_fsqm_exp' );
		$c = 'B';
		$average_format_string = '=AVERAGE(%3$s%1$d:%3$s%2$d)';
		foreach ( $mcq['settings']['columns'] as $c_key => $column ) {
			$tmp_arr[] = sprintf( $average_format_string, $data_cell_offset, count( $data_array ), $c++ );
		}
		$data_array[] = $tmp_arr;

		$activeSheet->fromArray( $data_array, null, 'A1', true );

		$this->set_row_iterator( count( $data_array ) );

		//Format the numbers
		$activeSheet->getStyle( 'B' . ( $data_cell_offset ) . ':' . $data_end_col . ( count( $data_array ) - 1 ) )->getNumberFormat()->setFormatCode( PHPExcel_Style_NumberFormat::FORMAT_NUMBER );
		$activeSheet->getStyle( 'B' . ( count( $data_array ) ) . ':' . $data_end_col . count( $data_array ) )->getNumberFormat()->setFormatCode( PHPExcel_Style_NumberFormat::FORMAT_NUMBER_00 );

		//Style it up
		$activeSheet->getColumnDimension( 'A' )->setWidth( 50 );
		for ( $i = 0, $c = 'A'; $i < count( $mcq['settings']['columns'] ); $i++, ++$c ) {
			$activeSheet->getColumnDimension( $c )->setWidth( 15 );
		}
		$activeSheet->getColumnDimension( $c )->setWidth( 15 );
		$activeSheet->getStyle( 'A1' )->applyFromArray( $this->formatStyles['title'] );
		$activeSheet->mergeCells( 'A1:' . $c . '1' );

		if ( $is_subtitle ) {
			$activeSheet->mergeCells( 'A2:' . $c . '2' );
			$activeSheet->getStyle( 'A2' )->applyFromArray( $this->formatStyles['subtitle'] );
		}

		$activeSheet->getStyle( sprintf( 'A%1$d:%2$s%1$d', ( $data_cell_offset - 1 ), $c ) )->applyFromArray( $this->formatStyles['data_head'] );
		$activeSheet->getStyle( sprintf( 'A%1$d:%2$s%1$d', count( $data_array ), $c ) )->applyFromArray( $this->formatStyles['data_head'] );

		$this->report_make_col_chart( $mcq['title'], $data_cell_offset - 1, count( $data_array ) - 1, 1, ++$c, '', 'B', $data_end_col, 'A' );
	}

	public function report_toggle( $mcq, $exp_data ) {
		//Init the data array
		$data_array = array();

		//Set the subtitle boolean
		$subtitle = false;

		//Feed the data_array
		$data_array[] = array( $mcq['title'], '', '' );
		if ( isset( $mcq['subtitle'] ) && '' != trim( $mcq['subtitle'] ) ) {
			$data_array[] = array( $mcq['subtitle'], '', '' );
			$subtitle = true;
		}
		$do_graph = true;
		//Calculate percentage field
		$percentage_total = 2;

		$percent_cell_offset = $subtitle ? 4 : 3;
		$percentage_format_string = '=B%d/SUM(B' . $percent_cell_offset . ':B' . ( $percentage_total + $percent_cell_offset - 1 ) . ')';

		if ( isset( $exp_data['on'] ) && isset( $exp_data['off'] ) ) {
			//Feed the data array
			$data_array[] = array( __( 'Toggle State', 'ipt_fsqm_exp' ), __( 'Count', 'ipt_fsqm_exp' ), __( 'Percent', 'ipt_fsqm_exp' ) );
			$data_array[] = array( strip_tags( $mcq['settings']['on'] ), $exp_data['on'], sprintf( $percentage_format_string, $percent_cell_offset ) );
			$data_array[] = array( strip_tags( $mcq['settings']['off'] ), $exp_data['off'], sprintf( $percentage_format_string, $percent_cell_offset + 1 ) );

			//Average
			//$avg_format_string = '=AVERAGE(%1$s' . $percent_cell_offset . ':%1$s' . count( $data_array ) . ')';
			$sum_format_string = '=SUM(%1$s' . $percent_cell_offset . ':%1$s' . count( $data_array ) . ')';
			$data_array[] = array( __( 'Total', 'ipt_fsqm_exp' ), sprintf( $sum_format_string, 'B' ), '' );
		} else {
			$do_graph = false;
			$data_array[] = array( __('Not enough data', 'ipt_fsqm_exp'), '', '' );
		}

		//Cache the activeSheet
		$activeSheet = $this->objPHPExcel->getActiveSheet();

		//Write data
		$activeSheet->fromArray( $data_array, null, 'A1', true );

		//Update the row iterator
		$this->set_row_iterator( count( $data_array ) );

		//Format it a little bit
		$activeSheet->getColumnDimension( 'A' )->setWidth( 70 );

		$activeSheet->getStyle( 'A1' )->applyFromArray( $this->formatStyles['title'] );
		$activeSheet->mergeCells( 'A1:C1' );
		if ( $subtitle ) {
			$activeSheet->mergeCells( 'A2:C2' );
			$activeSheet->getStyle( 'A2' )->applyFromArray( $this->formatStyles['subtitle'] );
			$activeSheet->getStyle( 'A3:C3' )->applyFromArray( $this->formatStyles['data_head'] );
		} else {
			$activeSheet->getStyle( 'A2:C2' )->applyFromArray( $this->formatStyles['data_head'] );
		}


		if ( $do_graph ) {
			//Format the percent and data column
			$activeSheet->getStyle( 'B' . ( $percent_cell_offset ) . ':B' . count( $data_array ) )->getNumberFormat()->setFormatCode( PHPExcel_Style_NumberFormat::FORMAT_NUMBER );
			$activeSheet->getStyle( 'C' . ( $percent_cell_offset ) . ':C' . count( $data_array ) )->getNumberFormat()->setFormatCode( PHPExcel_Style_NumberFormat::FORMAT_PERCENTAGE_00 );
			//Format the last row
			$activeSheet->getStyle( sprintf( 'A%1$d:C%1$d', count( $data_array ) ) )->applyFromArray( $this->formatStyles['data_head'] );
			//Generate the chart
			if ( $exp_data['on'] + $exp_data['off'] > 0 ) {
				$this->report_make_pie_chart( $mcq['title'], ( $subtitle ? 3 : 2 ), count( $data_array ) - 1, 1, 'D' );
			}
		}

	}

	public function report_sorting( $mcq, $exp_data ) {
		//Init the data array
		$data_array = array();

		//Set the subtitle boolean
		$subtitle = false;

		//Feed the data_array
		$data_array[] = array( $mcq['title'], '', '' );
		if ( isset( $mcq['subtitle'] ) && '' != trim( $mcq['subtitle'] ) ) {
			$data_array[] = array( $mcq['subtitle'], '', '' );
			$subtitle = true;
		}

		//Calculate percentage field
		$percentage_total = 2;
		$do_graph = true;

		$percent_cell_offset = $subtitle ? 4 : 3;
		$percentage_format_string = '=B%d/SUM(B' . $percent_cell_offset . ':B' . ( $percentage_total + $percent_cell_offset - 1 ) . ')';

		//Feed the data array
		if ( isset( $exp_data['preset'] ) && isset( $exp_data['other'] ) ) {
			$data_array[] = array( __( 'Sorting Order', 'ipt_fsqm_exp' ), __( 'Count', 'ipt_fsqm_exp' ), __( 'Percent', 'ipt_fsqm_exp' ) );
			$data_array[] = array( __( 'Predefined Order', 'ipt_fsqm_exp' ), $exp_data['preset'], sprintf( $percentage_format_string, $percent_cell_offset ) );
			$data_array[] = array( __( 'Custom Orders', 'ipt_fsqm_exp' ), $exp_data['other'], sprintf( $percentage_format_string, $percent_cell_offset + 1 ) );

			//Average
			//$avg_format_string = '=AVERAGE(%1$s' . $percent_cell_offset . ':%1$s' . count( $data_array ) . ')';
			$sum_format_string = '=SUM(%1$s' . $percent_cell_offset . ':%1$s' . count( $data_array ) . ')';
			$data_array[] = array( __( 'Total', 'ipt_fsqm_exp' ), sprintf( $sum_format_string, 'B' ), '' );
		} else {
			$do_graph = false;
			$data_array[] = array( __('Not enough data', 'ipt_fsqm_exp'), '', '' );
		}

		//Cache the activeSheet
		$activeSheet = $this->objPHPExcel->getActiveSheet();

		//Write data
		$activeSheet->fromArray( $data_array, null, 'A1', true );

		//Update the row iterator
		$this->set_row_iterator( count( $data_array ) );

		//Format it a little bit
		$activeSheet->getColumnDimension( 'A' )->setWidth( 70 );

		$activeSheet->getStyle( 'A1' )->applyFromArray( $this->formatStyles['title'] );
		$activeSheet->mergeCells( 'A1:C1' );
		if ( $subtitle ) {
			$activeSheet->mergeCells( 'A2:C2' );
			$activeSheet->getStyle( 'A2' )->applyFromArray( $this->formatStyles['subtitle'] );
			$activeSheet->getStyle( 'A3:C3' )->applyFromArray( $this->formatStyles['data_head'] );
		} else {
			$activeSheet->getStyle( 'A2:C2' )->applyFromArray( $this->formatStyles['data_head'] );
		}

		//Generate the chart
		if ( $do_graph ) {
			//Format the percent and data column
			$activeSheet->getStyle( 'B' . ( $percent_cell_offset ) . ':B' . count( $data_array ) )->getNumberFormat()->setFormatCode( PHPExcel_Style_NumberFormat::FORMAT_NUMBER );
			$activeSheet->getStyle( 'C' . ( $percent_cell_offset ) . ':C' . count( $data_array ) )->getNumberFormat()->setFormatCode( PHPExcel_Style_NumberFormat::FORMAT_PERCENTAGE_00 );
			//Format the last row
			$activeSheet->getStyle( sprintf( 'A%1$d:C%1$d', count( $data_array ) ) )->applyFromArray( $this->formatStyles['data_head'] );

			if ( $exp_data['preset'] + $exp_data['other'] > 0 ) {
				$this->report_make_pie_chart( $mcq['title'], ( $subtitle ? 3 : 2 ), count( $data_array ) - 1, 1, 'D' );
			}
		}

		//Now iterate and set the custom orders
		if ( isset( $exp_data['orders'] ) && is_array( $exp_data['orders'] ) && !empty( $exp_data['orders'] ) ) {
			$activeSheet->getCell( 'A' . $this->activeSheetRowIterator )->setValue( __( 'Sorting Order Breakdown', 'ipt_fsqm_exp' ) );
			$activeSheet->getCell( 'B' . $this->activeSheetRowIterator )->setValue( __( 'Count', 'ipt_fsqm_exp' ) );
			$activeSheet->getCell( 'C' . $this->activeSheetRowIterator )->setValue( __( 'Percent', 'ipt_fsqm_exp' ) );
			$activeSheet->getStyle( 'A' . $this->activeSheetRowIterator . ':C' . $this->activeSheetRowIterator )->applyFromArray( $this->formatStyles['data_head'] );
			$this->activeSheetRowIterator++;
			$order_cell_offset = $this->activeSheetRowIterator;
			$order_cell_count = count( $exp_data['orders'] );
			$oc_percentage_format_string = '=B%d/SUM(B' . $order_cell_offset . ':B' . ( $order_cell_count + $order_cell_offset - 1 ) . ')';
			foreach ( $exp_data['orders'] as $order => $count ) {
				$order_cell = array();
				$orders = explode( '-', $order );
				foreach ( $orders as $o_key ) {
					$o_key = (int) $o_key;
					if ( !isset( $mcq['settings']['options'][$o_key] ) ) {
						$order_cell[] = __( 'Deleted', 'ipt_fsqm_exp' );
					} else {
						$order_cell[] = strip_tags( $mcq['settings']['options'][$o_key]['label'] );
					}
				}
				$order_cell = implode( "\n", $order_cell );

				$activeSheet->getCell( 'A' . $this->activeSheetRowIterator )->setValue( $order_cell );
				$activeSheet->getStyle( 'A' . $this->activeSheetRowIterator )->getAlignment()->setWrapText( true );

				$activeSheet->getCell( 'B' . $this->activeSheetRowIterator )->setValue( $count );

				$activeSheet->getCell( 'C' . $this->activeSheetRowIterator )->setValue( sprintf( $oc_percentage_format_string, $this->activeSheetRowIterator ) );
				$this->activeSheetRowIterator++;
			}

			$activeSheet->getStyle( 'B' . $order_cell_offset . ':B' . ( $order_cell_offset + $order_cell_count - 1 ) )->getNumberFormat()->setFormatCode( PHPExcel_Style_NumberFormat::FORMAT_NUMBER );
			$activeSheet->getStyle( 'C' . $order_cell_offset . ':C' . ( $order_cell_offset + $order_cell_count - 1 ) )->getNumberFormat()->setFormatCode( PHPExcel_Style_NumberFormat::FORMAT_PERCENTAGE_00 );
		}
	}

	public function report_feedback_small( $freetype, $exp_data ) {
		$this->report_make_feedbacks( $freetype, $exp_data );
	}

	public function report_feedback_large( $freetype, $exp_data ) {
		$this->report_make_feedbacks( $freetype, $exp_data );
	}

	public function report_upload( $freetype, $exp_data ) {
		//Init the data array
		$data_array = array();

		//Set the subtitle boolean
		$subtitle = false;

		//Feed the data_array
		$data_array[] = array( $freetype['title'], '', '' );
		if ( isset( $freetype['subtitle'] ) && '' != trim( $freetype['subtitle'] ) ) {
			$data_array[] = array( $freetype['subtitle'], '', '' );
			$subtitle = true;
		}

		//Prep the heading
		$data_array[] = array( __( 'Name', 'ipt_fsqm_exp' ), __( 'Date', 'ipt_fsqm_exp' ), __( 'Uploads', 'ipt_fsqm_exp' ) );

		//Insert it
		$activeSheet = $this->objPHPExcel->getActiveSheet();
		$activeSheet->fromArray( $data_array );

		//Update row iterator
		$this->set_row_iterator( count( $data_array ) );

		//Format it
		$activeSheet->getColumnDimension( 'A' )->setWidth( 30 );
		$activeSheet->getColumnDimension( 'B' )->setWidth( 20 );
		$activeSheet->getColumnDimension( 'C' )->setWidth( 50 );

		$activeSheet->getStyle( 'A1' )->applyFromArray( $this->formatStyles['title'] );
		$activeSheet->mergeCells( 'A1:C1' );
		if ( $subtitle ) {
			$activeSheet->mergeCells( 'A2:C2' );
			$activeSheet->getStyle( 'A2' )->applyFromArray( $this->formatStyles['subtitle'] );
			$activeSheet->getStyle( 'A3:C3' )->applyFromArray( $this->formatStyles['data_head'] );
		} else {
			$activeSheet->getStyle( 'A2:C2' )->applyFromArray( $this->formatStyles['data_head'] );
		}

		if ( is_array( $exp_data ) && !empty( $exp_data ) ) {
			$date_format_string = get_option( 'date_format' ) . __( '\, ', 'ipt_fsqm_exp' ) . get_option( 'time_format' );
			foreach ( $exp_data as $other ) {
				$ref = admin_url( 'admin.php?page=ipt_fsqm_view_submission&id=' . $other['id'] );
				$date = date_i18n( $date_format_string, strtotime( $other['date'] ) );


				$activeSheet->getCell( 'A' . $this->activeSheetRowIterator )->setValue( $other['name'] )->getHyperlink()->setURL( $ref )->setTooltip( __( 'Click to view the complete submission.', 'ipt_fsqm_exp' ) );
				$activeSheet->getStyle( 'A' . $this->activeSheetRowIterator )->getFont()->getColor()->setARGB( PHPExcel_Style_Color::COLOR_DARKBLUE );

				$activeSheet->getCell( 'B' . $this->activeSheetRowIterator )->setValue( $date );

				$mergeStart = $this->activeSheetRowIterator;

				if ( ! empty( $other['uploads'] ) ) {
					foreach ( $other['uploads'] as $upload ) {
						$activeSheet->getCell( 'C' . $this->activeSheetRowIterator )->setValue( $upload['name'] )->getHyperlink()->setURL( $upload['guid'] )->setTooltip( sprintf( __( 'Click to download the file: %1$s.', 'ipt_fsqm_exp' ), $upload['filename'] ) );
						$activeSheet->getStyle( 'C' . $this->activeSheetRowIterator )->getFont()->getColor()->setARGB( PHPExcel_Style_Color::COLOR_DARKBLUE );
						$this->activeSheetRowIterator++;
					}
					// Merge the cells
					$activeSheet->mergeCells( 'A' . $mergeStart . ':A' . ( $this->activeSheetRowIterator - 1 ) );
					$activeSheet->mergeCells( 'B' . $mergeStart . ':B' . ( $this->activeSheetRowIterator - 1 ) );
				} else {
					$activeSheet->getCell( 'C' . $this->activeSheetRowIterator )->setValue( __( 'No files uploaded.', 'ipt_fsqm_exp' ) );
					$this->activeSheetRowIterator++;
				}

				// $this->activeSheetRowIterator++;
			}
		} else {
			$activeSheet->getCell( 'A' . $this->activeSheetRowIterator )->setValue( __( 'No entries yet.', 'ipt_fsqm_exp' ) );
			$this->activeSheetRowIterator++;
		}

		$activeSheet->getStyle( 'A1:C' . ( $this->activeSheetRowIterator - 1 ) )->getAlignment()->setVertical( PHPExcel_Style_Alignment::VERTICAL_TOP );
	}

	/*==========================================================================
	 * Worksheet related APIs
	 *========================================================================*/
	public function create_worksheet( $title, $comment = array() ) {
		if ( $this->activeSheetIndex == 0 ) {
			$this->objPHPExcel->setActiveSheetIndex( 0 );
			$this->objPHPExcel->getActiveSheet()->setTitle( $title );
			$this->activeSheetIndex++;
		} else {
			$tmpSheet = new PHPExcel_Worksheet( $this->objPHPExcel, $title );
			$this->objPHPExcel->addSheet( $tmpSheet, $this->activeSheetIndex++ );
			$this->objPHPExcel->setActiveSheetIndex( $this->activeSheetIndex - 1 );

		}
		$this->objPHPExcel->getActiveSheet()->setComments( $comment );
		$this->reset_row_iterator();
	}

	public function reset_row_iterator() {
		$this->activeSheetRowIterator = 1;
	}

	public function set_row_iterator( $index ) {
		$this->activeSheetRowIterator = (int) $index + 1;
	}

	public function get_row_iterator() {
		return $this->activeSheetRowIterator;
	}

	/*==========================================================================
	 * Element helpers
	 *========================================================================*/
	public function report_make_feedbacks( $freetype, $exp_data ) {
		//Init the data array
		$data_array = array();

		//Set the subtitle boolean
		$subtitle = false;

		//Feed the data_array
		$data_array[] = array( $freetype['title'], '', '', '', '' );
		if ( isset( $freetype['subtitle'] ) && '' != trim( $freetype['subtitle'] ) ) {
			$data_array[] = array( $freetype['subtitle'], '', '', '', '' );
			$subtitle = true;
		}

		//Prep the heading
		$data_array[] = array( __( 'Feedback', 'ipt_fsqm_exp' ), __( 'Name', 'ipt_fsqm_exp' ), __( 'Email', 'ipt_fsqm_exp' ), __( 'Phone', 'ipt_fsqm_exp' ), __( 'Date', 'ipt_fsqm_exp' ) );

		//Insert it
		$activeSheet = $this->objPHPExcel->getActiveSheet();
		$activeSheet->fromArray( $data_array );

		//Update row iterator
		$this->set_row_iterator( count( $data_array ) );

		//Format it
		$activeSheet->getColumnDimension( 'A' )->setWidth( 70 );
		$activeSheet->getColumnDimension( 'B' )->setWidth( 20 );
		$activeSheet->getColumnDimension( 'C' )->setWidth( 20 );
		$activeSheet->getColumnDimension( 'D' )->setWidth( 20 );
		$activeSheet->getColumnDimension( 'E' )->setWidth( 20 );

		$activeSheet->getStyle( 'A1' )->applyFromArray( $this->formatStyles['title'] );
		$activeSheet->mergeCells( 'A1:E1' );
		if ( $subtitle ) {
			$activeSheet->mergeCells( 'A2:E2' );
			$activeSheet->getStyle( 'A2' )->applyFromArray( $this->formatStyles['subtitle'] );
			$activeSheet->getStyle( 'A3:E3' )->applyFromArray( $this->formatStyles['data_head'] );
		} else {
			$activeSheet->getStyle( 'A2:E2' )->applyFromArray( $this->formatStyles['data_head'] );
		}

		if ( is_array( $exp_data ) && !empty( $exp_data ) ) {
			$date_format_string = get_option( 'date_format' ) . __( '\, ', 'ipt_fsqm_exp' ) . get_option( 'time_format' );
			foreach ( $exp_data as $other ) {
				$ref = admin_url( 'admin.php?page=ipt_fsqm_view_submission&id=' . $other['id'] );
				$value = str_replace( array( "\r", "\n\n" ), array( "", "\n" ), $other['value'] );
				$activeSheet->getCell( 'A' . $this->activeSheetRowIterator )->setValue( $value );
				$activeSheet->getStyle( 'A' . $this->activeSheetRowIterator )->getAlignment()->setWrapText( true );

				$activeSheet->getCell( 'B' . $this->activeSheetRowIterator )->setValue( $other['name'] )->getHyperlink()->setUrl( $ref )->setTooltip( __( 'Click to view the complete submission.', 'ipt_fsqm_exp' ) );
				$activeSheet->getStyle( 'B' . $this->activeSheetRowIterator )->getFont()->getColor()->setARGB( PHPExcel_Style_Color::COLOR_DARKBLUE );

				$activeSheet->getCell( 'C' . $this->activeSheetRowIterator )->setValue( $other['email'] );
				if ( is_email( $other['email'] ) ) {
					$activeSheet->getCell( 'C' . $this->activeSheetRowIterator )->getHyperlink()->setUrl( 'mailto:' . $other['email'] )->setTooltip( __( 'Click to email the user.', 'ipt_fsqm_exp' ) );
					$activeSheet->getStyle( 'C' . $this->activeSheetRowIterator )->getFont()->getColor()->setARGB( PHPExcel_Style_Color::COLOR_DARKBLUE );
				}

				$activeSheet->getCell( 'D' . $this->activeSheetRowIterator )->setValue( $other['phone'] );

				$activeSheet->getCell( 'E' . $this->activeSheetRowIterator )->setValue( date_i18n( $date_format_string, strtotime( $other['date'] ) ) );

				$this->activeSheetRowIterator++;
			}
		} else {
			$activeSheet->getCell( 'A' . $this->activeSheetRowIterator )->setValue( __( 'No entries yet.', 'ipt_fsqm_exp' ) );
			$this->activeSheetRowIterator++;
		}
	}
	public function report_make_spinners_ratings( $mcq, $exp_data ) {
		$activeSheet = $this->objPHPExcel->getActiveSheet();
		$is_subtitle = false;
		$data_array = array();
		$data_array[] = array( $mcq['title'], '', '', '' );
		if ( trim( $mcq['subtitle'] ) != '' ) {
			$data_array[] = array( $mcq['subtitle'], '', '', '' );
			$is_subtitle = true;
		}
		$activeSheet->fromArray( $data_array, null, 'A1', true );

		$this->set_row_iterator( count( $data_array ) );

		$activeSheet->getStyle( 'A1' )->applyFromArray( $this->formatStyles['title'] );
		$activeSheet->mergeCells( 'A1:E1' );

		if ( $is_subtitle ) {
			$activeSheet->mergeCells( 'A2:E2' );
			$activeSheet->getStyle( 'A2' )->applyFromArray( $this->formatStyles['subtitle'] );
		}

		foreach ( $mcq['settings']['options'] as $o_key => $option ) {
			if ( ! isset( $exp_data["$o_key"] ) ) {
				$exp_data["$o_key"] = array();
			}
			$this->report_make_slider( $this->activeSheetRowIterator, $option, '', $exp_data["$o_key"], $mcq['settings'], array( '#', __( 'Value', 'ipt_fsqm_exp' ), __( 'Count', 'ipt_fsqm_exp' ), __( 'Percent', 'ipt_fsqm_exp' ) ), $o_key );
		}
	}
	public function report_make_mcqs( $mcq, $exp_data ) {
		//Init the data array
		$data_array = array();

		//Set the subtitle boolean
		$subtitle = false;

		//Feed the data_array
		$data_array[] = array( $mcq['title'], '', '' );
		if ( isset( $mcq['subtitle'] ) && '' != trim( $mcq['subtitle'] ) ) {
			$data_array[] = array( $mcq['subtitle'], '', '' );
			$subtitle = true;
		}

		//Calculate percentage field
		$percentage_total = count( $mcq['settings']['options'] );
		if ( isset( $mcq['settings']['others'] ) && true == $mcq['settings']['others'] ) {
			$percentage_total++;
		}
		$percent_cell_offset = $subtitle ? 4 : 3;
		$percentage_format_string = '=B%d/SUM(B' . $percent_cell_offset . ':B' . ( $percentage_total + $percent_cell_offset - 1 ) . ')';

		//Feed the data array
		$data_array[] = array( __( 'Options', 'ipt_fsqm_exp' ), __( 'Count', 'ipt_fsqm_exp' ), __( 'Percent', 'ipt_fsqm_exp' ) );
		$i = 0;
		$do_graph = true;
		$total = 0;
		foreach ( $mcq['settings']['options'] as $o_key => $option ) {
			if ( ! isset( $exp_data["$o_key"] ) ) {
				continue;
			}
			$total += (int) $exp_data["$o_key"];
			$data_array[] = array( strip_tags( $option['label'] ), (int) $exp_data["$o_key"], sprintf( $percentage_format_string, $percent_cell_offset + ( $i++ ) ) );
		}

		//Feed more if others is set to true
		if ( isset( $mcq['settings']['others'] ) && true == $mcq['settings']['others'] ) {
			$o_count = isset( $exp_data['others'] ) ? (int) $exp_data['others'] : 0;
			$total += $o_count;
			$data_array[] = array( strip_tags( $mcq['settings']['o_label'] ), $o_count, sprintf( $percentage_format_string, $percent_cell_offset + ( $i++ ) ) );
		}

		if ( $i == 0 ) {
			$do_graph = false;
			$data_array[] = array( __( 'Not enough data.', 'ipt_fsqm_exp' ), '', '' );
		}

		//Average
		//$avg_format_string = '=AVERAGE(%1$s' . $percent_cell_offset . ':%1$s' . count( $data_array ) . ')';

		if ( $do_graph ) {
			$sum_format_string = '=SUM(%1$s' . $percent_cell_offset . ':%1$s' . count( $data_array ) . ')';
			$data_array[] = array( __( 'Total', 'ipt_fsqm_exp' ), sprintf( $sum_format_string, 'B' ), '' );
		}

		//Cache the activeSheet
		$activeSheet = $this->objPHPExcel->getActiveSheet();

		//Write data
		$activeSheet->fromArray( $data_array, null, 'A1', true );

		//Update the row iterator
		$this->set_row_iterator( count( $data_array ) );

		//Format it a little bit
		$activeSheet->getColumnDimension( 'A' )->setWidth( 70 );

		$activeSheet->getStyle( 'A1' )->applyFromArray( $this->formatStyles['title'] );
		$activeSheet->mergeCells( 'A1:C1' );
		if ( $subtitle ) {
			$activeSheet->mergeCells( 'A2:C2' );
			$activeSheet->getStyle( 'A2' )->applyFromArray( $this->formatStyles['subtitle'] );
			$activeSheet->getStyle( 'A3:C3' )->applyFromArray( $this->formatStyles['data_head'] );
		} else {
			$activeSheet->getStyle( 'A2:C2' )->applyFromArray( $this->formatStyles['data_head'] );
		}

		//Generate the chart
		if ( $do_graph ) {
			//Format the percent and data column
			$activeSheet->getStyle( 'B' . ( $percent_cell_offset ) . ':B' . count( $data_array ) )->getNumberFormat()->setFormatCode( PHPExcel_Style_NumberFormat::FORMAT_NUMBER );
			$activeSheet->getStyle( 'C' . ( $percent_cell_offset ) . ':C' . count( $data_array ) )->getNumberFormat()->setFormatCode( PHPExcel_Style_NumberFormat::FORMAT_PERCENTAGE_00 );

			//Style the last row
			$activeSheet->getStyle( sprintf( 'A%1$d:C%1$d', count( $data_array ) ) )->applyFromArray( $this->formatStyles['data_head'] );

			//Finally the chart
			if ( $total > 0 ) {
				$this->report_make_pie_chart( $mcq['title'], ( $subtitle ? 3 : 2 ), count( $data_array ) - 1, 1, 'D' );
			}
		}

		//Add others data
		if ( isset( $mcq['settings']['others'] ) && true == $mcq['settings']['others'] ) {
			$activeSheet->getColumnDimension( 'B' )->setWidth( 20 );
			$activeSheet->getColumnDimension( 'C' )->setWidth( 20 );
			$head_row = $this->activeSheetRowIterator;
			if ( $this->type == 'xlsx' || $this->type == 'xls' ) {
				$others_data = array( $mcq['settings']['o_label'], __( 'Name', 'ipt_fsqm_exp' ), __( 'Email', 'ipt_fsqm_exp' ), __( 'Reference ID / Link', 'ipt_fsqm_exp' ) );
			} else {
				$others_data = array( __( 'Reference ID / Link', 'ipt_fsqm_exp' ), __( 'Name', 'ipt_fsqm_exp' ), __( 'Email', 'ipt_fsqm_exp' ), $mcq['settings']['o_label'] );
			}

			$activeSheet->fromArray( $others_data, null, 'A' . $this->activeSheetRowIterator );
			$this->activeSheetRowIterator++;

			if ( isset( $exp_data['others_data'] ) && is_array( $exp_data['others_data'] ) ) {
				foreach ( $exp_data['others_data'] as $other ) {
					$ref = admin_url( 'admin.php?page=ipt_fsqm_view_submission&id=' . $other['id'] );

					if ( $this->type == 'xlsx' || $this->type == 'xls' ) {
						$activeSheet->getCell( 'A' . $this->activeSheetRowIterator )->setValue( $other['value'] );
						$activeSheet->getStyle( 'A' . $this->activeSheetRowIterator )->getAlignment()->setWrapText( true );

						$activeSheet->getCell( 'D' . $this->activeSheetRowIterator )->setValue( '[' . $other['id'] . '] ' . __( 'View full data', 'ipt_fsqm_exp' ) )->getHyperlink()->setUrl( $ref )->setTooltip( __( 'Click to view the complete submission.', 'ipt_fsqm_exp' ) );
						$activeSheet->getStyle( 'D' . $this->activeSheetRowIterator )->getFont()->getColor()->setARGB( PHPExcel_Style_Color::COLOR_DARKBLUE );
					} else {
						$activeSheet->getCell( 'D' . $this->activeSheetRowIterator )->setValue( $other['value'] );
						$activeSheet->getStyle( 'D' . $this->activeSheetRowIterator )->getAlignment()->setWrapText( true );

						$activeSheet->getCell( 'A' . $this->activeSheetRowIterator )->setValue( '[' . $other['id'] . '] ' . __( 'View full data', 'ipt_fsqm_exp' ) )->getHyperlink()->setUrl( $ref )->setTooltip( __( 'Click to view the complete submission.', 'ipt_fsqm_exp' ) );
						$activeSheet->getStyle( 'A' . $this->activeSheetRowIterator )->getFont()->getColor()->setARGB( PHPExcel_Style_Color::COLOR_DARKBLUE );
					}

					$activeSheet->getCell( 'B' . $this->activeSheetRowIterator )->setValue( $other['name'] );

					$activeSheet->getCell( 'C' . $this->activeSheetRowIterator )->setValue( $other['email'] )->getHyperlink()->setUrl( 'mailto:' . $other['email'] )->setTooltip( __( 'Click to email the user.', 'ipt_fsqm_exp' ) );
					$activeSheet->getStyle( 'C' . $this->activeSheetRowIterator )->getFont()->getColor()->setARGB( PHPExcel_Style_Color::COLOR_DARKBLUE );

					$this->activeSheetRowIterator++;
				}
			} else {
				$others_data = array( __( 'No entries yet.', 'ipt_fsqm_exp' ), '', '', '' );
				$activeSheet->fromArray( $others_data, null, 'A' . $this->activeSheetRowIterator );
				$this->activeSheetRowIterator++;
			}


			//Style it
			$activeSheet->getStyle( sprintf( 'A%1$d:D%1$d', $head_row ) )->applyFromArray( $this->formatStyles['data_head'] );
		}
	}

	/**
	 * Make report for slider and star type elements
	 *
	 * @param int     $start_row  The start row number
	 * @param string  $title      The title of the element
	 * @param string  $subtitle   The subtitle of the element, pass '' to ignore
	 * @param array   $data       Associative array of the data in value => count
	 * @param array   $settings   Associative array of the settings
	 * @param array   $head_array array of values for the data head row
	 * @return void
	 */
	public function report_make_slider( $start_row, $title, $subtitle, $data, $settings, $head_array, $suffix = '' ) {
		//init the variables
		$data_array = array();
		$is_subtitle = false;
		$activeSheet = $this->objPHPExcel->getActiveSheet();

		//put the title
		$data_array[] = array( $title, '', '', '' );
		//put the subtitle
		if ( trim( $subtitle ) != '' ) {
			$is_subtitle = true;
			$data_array[] = array( $subtitle, '', '', '' );
		}

		$data_cell_offset = $is_subtitle ? 3 : 2;
		$data_cell_offset += $start_row;
		$percentage_total = count( $data );

		//put the value vs count
		$data_array[] = $head_array;
		krsort( $data );
		$i = 1;
		$do_graph = true;
		$percentage_format_string = '=C%d/SUM(C' . ( $data_cell_offset ). ':C' . ( $percentage_total + $data_cell_offset - 1 ) . ')';
		if ( ! empty ( $data ) ) {
			foreach ( $data as $value => $count ) {
				$data_array[] = array( '#' . ( $i ), $value, $count, sprintf( $percentage_format_string, ( $data_cell_offset - 1 + $i ) ) );
				$i++;
			}
			//put the average and total
			$average_format_string = '=SUMPRODUCT(B%1$d:B%2$d,C%1$d:C%2$d)/SUM(C%1$d:C%2$d)';
			$sum_format_string = '=SUM(C%1$d:C%2$d)';

			$data_array[] = array( __( 'Average / Total', 'ipt_fsqm_exp' ), sprintf( $average_format_string, $data_cell_offset, $start_row + count( $data_array ) - 1 ), sprintf( $sum_format_string, $data_cell_offset, $start_row + count( $data_array ) -1 ), '' );
		} else {
			$do_graph = false;

			$data_array[] = array( __( 'Not enough data', 'ipt_fsqm_exp' ), '', '', '' );
		}

		//Add it
		$activeSheet->fromArray( $data_array, null, 'A' . $start_row, true );

		//Update the row iterator
		$this->set_row_iterator( $start_row + count( $data_array ) - 1 );

		//Style it a little bit
		$activeSheet->getColumnDimension( 'A' )->setWidth( 50 );
		$activeSheet->getStyle( 'A' . $start_row )->applyFromArray( $this->formatStyles['title'] );
		$activeSheet->mergeCells( 'A' . $start_row . ':D' . $start_row );

		if ( $is_subtitle ) {
			$activeSheet->mergeCells( 'A' . ( $start_row + 1 ) . ':D' . ( $start_row + 1 ) );
			$activeSheet->getStyle( 'A' . ( $start_row + 1 ) )->applyFromArray( $this->formatStyles['subtitle'] );
		}

		$activeSheet->getStyle( sprintf( 'A%1$d:D%1$d', ( $data_cell_offset - 1 ) ) )->applyFromArray( $this->formatStyles['data_head'] );

		if ( $do_graph ) {
			//Format the numbers
			$activeSheet->getStyle( 'B' . $data_cell_offset . ':B' . ( $start_row + count( $data_array ) - 1 ) )->getNumberFormat()->setFormatCode( PHPExcel_Style_NumberFormat::FORMAT_NUMBER_00 );
			$activeSheet->getStyle( 'C' . $data_cell_offset . ':C' . ( $start_row + count( $data_array ) - 1 ) )->getNumberFormat()->setFormatCode( PHPExcel_Style_NumberFormat::FORMAT_NUMBER );
			$activeSheet->getStyle( 'D' . $data_cell_offset . ':D' . ( $start_row + count( $data_array ) - 1 ) )->getNumberFormat()->setFormatCode( PHPExcel_Style_NumberFormat::FORMAT_PERCENTAGE_00 );

			//Format the last row
			$activeSheet->getStyle( sprintf( 'A%1$d:D%1$d', ( $this->activeSheetRowIterator - 1 ) ) )->applyFromArray( $this->formatStyles['data_head'] );

			$this->report_make_col_chart( $title, $data_cell_offset - 1, count( $data ) + $data_cell_offset - 1, $start_row, 'E', $suffix );
		}
	}

	/**
	 * Make report for range type elements
	 *
	 * @param int     $start_row  The start row number
	 * @param string  $title      The title of the element
	 * @param string  $subtitle   The subtitle of the element, pass '' to ignore
	 * @param array   $data       Associative array of the data in value => count
	 * @param array   $settings   Associative array of the settings
	 * @param array   $head_array array of values for the data head row
	 * @return void
	 */
	public function report_make_range( $start_row, $title, $subtitle, $data, $settings, $head_array, $suffix = '' ) {
		//init the variables
		$data_array = array();
		$is_subtitle = false;
		$activeSheet = $this->objPHPExcel->getActiveSheet();

		//put the title
		$data_array[] = array( $title, '', '', '', '', '', '' );
		//put the subtitle
		if ( trim( $subtitle ) != '' ) {
			$is_subtitle = true;
			$data_array[] = array( $subtitle, '', '', '', '', '', '' );
		}

		$data_cell_offset = $is_subtitle ? 3 : 2;
		$data_cell_offset += $start_row;
		$percentage_total = count( $data );

		//put the value vs count
		$data_array[] = $head_array;
		$i = 1;
		$do_graph = true;
		arsort( $data );
		$percentage_format_string = '=B%d/SUM(B' . ( $data_cell_offset ). ':B' . ( $percentage_total + $data_cell_offset - 1 ) . ')';

		if ( !empty( $data ) ) {
			foreach ( $data as $values => $count ) {
				$value = explode( ',', $values );
				if ( !is_array( $value ) || count( $value ) < 2 ) {
					continue;
				}
				$data_array[] = array( '#' . ( $i ), $count, $value[1], $value[0], $settings['min'], $settings['max'], sprintf( $percentage_format_string, ( $data_cell_offset - 1 + $i ) ) );
				$i++;
			}

			//put the average and total
			//$average_format_string = '=AVERAGE(%3$s%1$d:%3$s%2$d)';
			//I know it is messy, and won't even work in XLS, what can we do??
			$average_format_string = '=SUMPRODUCT(%3$s%1$d:%3$s%2$d,B%1$d:B%2$d)/SUM(B%1$d:B%2$d)';
			$sum_format_string = '=SUM(%3$s%1$d:%3$s%2$d)';

			$data_array[] = array(
				__( 'Total / Average', 'ipt_fsqm_exp' ),
				sprintf( $sum_format_string, $data_cell_offset, $start_row + count( $data_array ) - 1, 'B' ),
				sprintf( $average_format_string, $data_cell_offset, $start_row + count( $data_array ) - 1, 'C' ),
				sprintf( $average_format_string, $data_cell_offset, $start_row + count( $data_array ) - 1, 'D' ),
				'',
				'',
				''
			);
		} else {
			$do_graph = false;
			$data_array[] = array( __( 'Not enough data', 'ipt_fsqm_exp' ), '', '', '', '', '', '' );
		}

		//Add it
		$activeSheet->fromArray( $data_array, null, 'A' . $start_row, true );

		//Update the row iterator
		$this->set_row_iterator( $start_row + count( $data_array ) - 1 );


		//Style it a little bit
		$activeSheet->getColumnDimension( 'A' )->setWidth( 50 );
		$activeSheet->getStyle( 'A' . $start_row )->applyFromArray( $this->formatStyles['title'] );
		$activeSheet->mergeCells( 'A' . $start_row . ':G' . $start_row );

		if ( $is_subtitle ) {
			$activeSheet->mergeCells( 'A' . ( $start_row + 1 ) . ':G' . ( $start_row + 1 ) );
			$activeSheet->getStyle( 'A' . ( $start_row + 1 ) )->applyFromArray( $this->formatStyles['subtitle'] );
		}

		$activeSheet->getStyle( sprintf( 'A%1$d:G%1$d', ( $data_cell_offset - 1 ) ) )->applyFromArray( $this->formatStyles['data_head'] );



		if ( $do_graph ) {
			//Format the numbers
			$activeSheet->getStyle( 'B' . $data_cell_offset . ':B' . ( $start_row + count( $data_array ) - 1 ) )->getNumberFormat()->setFormatCode( PHPExcel_Style_NumberFormat::FORMAT_NUMBER );
			$activeSheet->getStyle( 'C' . $data_cell_offset . ':F' . ( $start_row + count( $data_array ) - 1 ) )->getNumberFormat()->setFormatCode( PHPExcel_Style_NumberFormat::FORMAT_NUMBER_00 );
			$activeSheet->getStyle( 'G' . $data_cell_offset . ':G' . ( $start_row + count( $data_array ) - 1 ) )->getNumberFormat()->setFormatCode( PHPExcel_Style_NumberFormat::FORMAT_PERCENTAGE_00 );

			//Format the last row
			$activeSheet->getStyle( sprintf( 'A%1$d:G%1$d', ( $this->activeSheetRowIterator - 1 ) ) )->applyFromArray( $this->formatStyles['data_head'] );

			$this->report_make_stock_chart( $title, $data_cell_offset - 1, count( $data ) + $data_cell_offset - 1, $start_row, 'H', $suffix );
		}
	}


	/*==========================================================================
	 * Chart Generator shortcuts
	 *========================================================================*/
	public function report_make_stock_chart( $title, $data_start, $data_end, $chart_start_row, $chart_start_col, $suffix = '' ) {
		$data_total = $data_end - $data_start - 1;

		$chart_end_col = PHPExcel_Cell::stringFromColumnIndex( PHPExcel_Cell::columnIndexFromString( $chart_start_col ) + 6 );
		$chart_start = $chart_start_col . $chart_start_row;
		$chart_row_end = max( array( $chart_start_row + 8, $data_end ) );
		$chart_end = $chart_end_col . $chart_row_end;

		$activeSheet = $this->objPHPExcel->getActiveSheet();

		$this->set_row_iterator( max( array( $this->activeSheetRowIterator - 1, $chart_row_end ) ) );

		$chart_cell_range = PHPExcel_Cell::rangeDimension( $chart_start . ':' . $chart_end );

		$chart_image_dimension = array(
			round( $chart_cell_range[0] * self::DIMENSION_CHART_X ),
			round( $chart_cell_range[1] * self::DIMENSION_CHART_Y ),
		);

		$worksheet = $activeSheet->getTitle();

		//create the chart variables
		$data_series_labels = array(
			new PHPExcel_Chart_DataSeriesValues( 'String', $worksheet . '!$B$' . $data_start, NULL, 1 ),
			new PHPExcel_Chart_DataSeriesValues( 'String', $worksheet . '!$C$' . $data_start, NULL, 1 ),
			new PHPExcel_Chart_DataSeriesValues( 'String', $worksheet . '!$D$' . $data_start, NULL, 1 ),
			new PHPExcel_Chart_DataSeriesValues( 'String', $worksheet . '!$E$' . $data_start, NULL, 1 ),
			new PHPExcel_Chart_DataSeriesValues( 'String', $worksheet . '!$F$' . $data_start, NULL, 1 ),
		);
		$data_series_values = array(
			new PHPExcel_Chart_DataSeriesValues( 'Number', $worksheet . '!$B$' . ( $data_start + 1 ) . ':$B$' . $data_end, NULL, $data_total ),
			new PHPExcel_Chart_DataSeriesValues( 'Number', $worksheet . '!$C$' . ( $data_start + 1 ) . ':$C$' . $data_end, NULL, $data_total ),
			new PHPExcel_Chart_DataSeriesValues( 'Number', $worksheet . '!$D$' . ( $data_start + 1 ) . ':$D$' . $data_end, NULL, $data_total ),
			new PHPExcel_Chart_DataSeriesValues( 'Number', $worksheet . '!$E$' . ( $data_start + 1 ) . ':$E$' . $data_end, NULL, $data_total ),
			new PHPExcel_Chart_DataSeriesValues( 'Number', $worksheet . '!$F$' . ( $data_start + 1 ) . ':$F$' . $data_end, NULL, $data_total ),
		);
		$x_axis_ticks = array(
			new PHPExcel_Chart_DataSeriesValues( 'String', $worksheet . '!$A$' . ( $data_start + 1 ) . ':$A$' . $data_end, NULL, $data_total ),
		);

		//create the series
		$series = new PHPExcel_Chart_DataSeries(
			PHPExcel_Chart_DataSeries::TYPE_STOCKCHART,
			null,
			range( 0, count( $data_series_values ) - 1 ),
			$data_series_labels,
			$x_axis_ticks,
			$data_series_values
		);
		$series->setPlotDirection( PHPExcel_Chart_DataSeries::DIRECTION_BAR );

		//Create plot area
		$plotarea = new PHPExcel_Chart_PlotArea( NULL, array( $series ) );

		//set the legend
		$legend = new PHPExcel_Chart_Legend( PHPExcel_Chart_Legend::POSITION_RIGHT, NULL, false );
		$chart_title = new PHPExcel_Chart_Title( $title );
		$x_axis_label = NULL;
		$y_axis_label = new PHPExcel_Chart_Title( __( 'Ranges', 'ipt_fsqm_exp' ) );

		$chart = new PHPExcel_Chart(
			'surveychart' . $this->surveyIterator . $suffix,
			$chart_title,
			$legend,
			$plotarea,
			true,
			0,
			$x_axis_label,
			$y_axis_label
		);
		$chart->setTopLeftPosition( $chart_start );
		$chart->setBottomRightPosition( $chart_end );

		$activeSheet->addChart( $chart );

		if ( $this->type == 'xlsx' ) {

		} else {
			$chart_image = $this->graphpath . '/' . wp_unique_filename( $this->graphpath, sanitize_file_name( $title  . '.png' ) );
			$chart->render( $chart_image, $chart_image_dimension[0], $chart_image_dimension[1] );

			$objDrawing = new PHPExcel_Worksheet_Drawing();
			$objDrawing->setName( $title );
			$objDrawing->setPath( $chart_image );
			$objDrawing->setWidthAndHeight( $chart_image_dimension[0], $chart_image_dimension[1] );
			$objDrawing->setCoordinates( $chart_start );
			$objDrawing->setWorksheet( $activeSheet );
			$merge_cells = sprintf( '%1$s%2$d:%1$s%3$d', $chart_start_col, $chart_start_row, $chart_row_end );
			$activeSheet->mergeCells( $merge_cells );
		}
	}


	public function report_make_col_chart( $title, $data_start, $data_end, $chart_start_row, $chart_start_col, $suffix = '', $data_start_col = 'B', $data_end_col = 'C', $x_ticks_col = 'A' ) {
		$data_total = $data_end - $data_start - 1;

		$chart_end_col = PHPExcel_Cell::stringFromColumnIndex( PHPExcel_Cell::columnIndexFromString( $chart_start_col ) + 6 );
		$chart_start = $chart_start_col . $chart_start_row;
		$chart_row_end = max( array( $chart_start_row + 8, $data_end ) );
		$chart_end = $chart_end_col . $chart_row_end;

		$activeSheet = $this->objPHPExcel->getActiveSheet();

		$this->set_row_iterator( max( array( $this->activeSheetRowIterator - 1, $chart_row_end ) ) );

		$chart_cell_range = PHPExcel_Cell::rangeDimension( $chart_start . ':' . $chart_end );

		$chart_image_dimension = array(
			round( $chart_cell_range[0] * self::DIMENSION_CHART_X ),
			round( $chart_cell_range[1] * self::DIMENSION_CHART_Y ),
		);

		$worksheet = $activeSheet->getTitle();

		//create the chart variables
		$data_series_labels = array(
			// new PHPExcel_Chart_DataSeriesValues( 'String', $worksheet . '!$B$' . $data_start, NULL, 1 ),
			// new PHPExcel_Chart_DataSeriesValues( 'String', $worksheet . '!$C$' . $data_start, NULL, 1 ),
		);
		for ( $i = $data_start_col; $i !== $data_end_col; $i++ ) {
			$data_series_labels[] = new PHPExcel_Chart_DataSeriesValues( 'String', $worksheet . '!$' . $i . '$' . $data_start, NULL, 1 );
		}
		$data_series_labels[] = new PHPExcel_Chart_DataSeriesValues( 'String', $worksheet . '!$' . $data_end_col . '$' . $data_start, NULL, 1 );

		$data_series_values = array(
			// new PHPExcel_Chart_DataSeriesValues( 'Number', $worksheet . '!$B$' . ( $data_start + 1 ) . ':$B$' . $data_end, NULL, $data_total ),
			// new PHPExcel_Chart_DataSeriesValues( 'Number', $worksheet . '!$C$' . ( $data_start + 1 ) . ':$C$' . $data_end, NULL, $data_total ),
		);
		for ( $i = $data_start_col; $i !== $data_end_col; $i++ ) {
			$data_series_values[] = new PHPExcel_Chart_DataSeriesValues( 'Number', $worksheet . '!$' . $i . '$' . ( $data_start + 1 ) . ':$' . $i . '$' . $data_end, NULL, $data_total );
		}
		$data_series_values[] = new PHPExcel_Chart_DataSeriesValues( 'Number', $worksheet . '!$' . $data_end_col . '$' . ( $data_start + 1 ) . ':$' . $data_end_col . '$' . $data_end, NULL, $data_total );

		$x_axis_ticks = array(
			new PHPExcel_Chart_DataSeriesValues( 'String', $worksheet . '!$' . $x_ticks_col . '$' . ( $data_start + 1 ) . ':$' . $x_ticks_col . '$' . $data_end, NULL, $data_total ),
		);

		//create the series
		$series = new PHPExcel_Chart_DataSeries(
			PHPExcel_Chart_DataSeries::TYPE_BARCHART,
			PHPExcel_Chart_DataSeries::GROUPING_CLUSTERED,
			range( 0, count( $data_series_values ) - 1 ),
			$data_series_labels,
			$x_axis_ticks,
			$data_series_values
		);
		$series->setPlotDirection( PHPExcel_Chart_DataSeries::DIRECTION_COL );

		//Create plot area
		$plotarea = new PHPExcel_Chart_PlotArea( NULL, array( $series ) );

		//set the legend
		$legend = new PHPExcel_Chart_Legend( PHPExcel_Chart_Legend::POSITION_RIGHT, NULL, false );
		$chart_title = new PHPExcel_Chart_Title( $title );

		$chart = new PHPExcel_Chart(
			'surveychart' . $this->surveyIterator . $suffix,
			$chart_title,
			$legend,
			$plotarea,
			true,
			0,
			NULL,
			NULL
		);
		$chart->setTopLeftPosition( $chart_start );
		$chart->setBottomRightPosition( $chart_end );

		$activeSheet->addChart( $chart );

		if ( $this->type == 'xlsx' ) {

		} else {
			$chart_image = $this->graphpath . '/' . wp_unique_filename( $this->graphpath, sanitize_file_name( $title  . '.png' ) );
			$chart->render( $chart_image, $chart_image_dimension[0], $chart_image_dimension[1] );
			$objDrawing = new PHPExcel_Worksheet_Drawing();
			$objDrawing->setName( $title );
			$objDrawing->setPath( $chart_image );
			$objDrawing->setWidthAndHeight( $chart_image_dimension[0], $chart_image_dimension[1] );
			$objDrawing->setCoordinates( $chart_start );
			$objDrawing->setWorksheet( $activeSheet );
			$merge_cells = sprintf( '%1$s%2$d:%1$s%3$d', $chart_start_col, $chart_start_row, $chart_row_end );
			$activeSheet->mergeCells( $merge_cells );
		}
	}

	public function report_make_pie_chart( $title, $data_start, $data_end, $chart_start_row, $chart_start_col, $suffix = '' ) {
		$data_total = $data_end - $data_start;

		$chart_end_col = PHPExcel_Cell::stringFromColumnIndex( PHPExcel_Cell::columnIndexFromString( $chart_start_col ) + 6 );
		$chart_start = $chart_start_col . $chart_start_row;
		$chart_row_end = max( array( $chart_start_row + 8, $data_end ) );
		$chart_end = $chart_end_col . $chart_row_end;

		$activeSheet = $this->objPHPExcel->getActiveSheet();

		$this->set_row_iterator( max( array( $this->activeSheetRowIterator - 1, $chart_row_end ) ) );

		$chart_cell_range = PHPExcel_Cell::rangeDimension( $chart_start . ':' . $chart_end );

		$chart_image_dimension = array(
			round( $chart_cell_range[0] * self::DIMENSION_CHART_X ),
			round( $chart_cell_range[1] * self::DIMENSION_CHART_Y ),
		);

		$worksheet = $activeSheet->getTitle();
		$data_series_labels = array(
			new PHPExcel_Chart_DataSeriesValues( 'String', $worksheet . '!$A$' . $data_start, NULL, 1 ),
		);
		$x_axis_ticks = array(
			new PHPExcel_Chart_DataSeriesValues( 'String', $worksheet . '!$A$' . ( $data_start + 1 ) . ':$A$' . $data_end, NULL, $data_total ),
		);
		$data_series_values = array(
			new PHPExcel_Chart_DataSeriesValues( 'Number', $worksheet . '!$B$' . ( $data_start + 1 ) . ':$B$' . $data_end, NULL, $data_total ),
		);
		$series = new PHPExcel_Chart_DataSeries(
			PHPExcel_Chart_DataSeries::TYPE_PIECHART,
			NULL,
			range( 0, count( $data_series_values ) - 1 ),
			$data_series_labels,
			$x_axis_ticks,
			$data_series_values
		);
		$series->setPlotDirection( PHPExcel_Chart_DataSeries::DIRECTION_COL );

		$layout = new PHPExcel_Chart_Layout();
		$layout->setShowVal( FALSE );
		$layout->setShowPercent( TRUE );

		$plotarea = new PHPExcel_Chart_PlotArea( $layout, array( $series ) );

		$legend = new PHPExcel_Chart_Legend( PHPExcel_Chart_Legend::POSITION_RIGHT, $layout, true );
		$chart_title = new PHPExcel_Chart_Title( $title );

		$chart = new PHPExcel_Chart(
			'surveychart' . $this->surveyIterator . $suffix,
			$chart_title,
			$legend,
			$plotarea,
			true,
			0,
			NULL,
			NULL
		);
		$chart->setTopLeftPosition( $chart_start );
		$chart->setBottomRightPosition( $chart_end );

		$activeSheet->addChart( $chart );

		if ( $this->type == 'xlsx' ) {

		} else {
			$chart_image = $this->graphpath . '/' . wp_unique_filename( $this->graphpath, sanitize_file_name( $title  . '.png' ) );
			$chart->render( $chart_image, $chart_image_dimension[0], $chart_image_dimension[1] );
			$objDrawing = new PHPExcel_Worksheet_Drawing();
			$objDrawing->setName( $title );
			$objDrawing->setPath( $chart_image );
			$objDrawing->setWidthAndHeight( $chart_image_dimension[0], $chart_image_dimension[1] );
			$objDrawing->setCoordinates( $chart_start );
			$objDrawing->setWorksheet( $activeSheet );
			$merge_cells = sprintf( '%1$s%2$d:%1$s%3$d', $chart_start_col, $chart_start_row, $chart_row_end );
			$activeSheet->mergeCells( $merge_cells );
		}
	}

	/*==========================================================================
	 * File handling & ZIP APIs
	 *========================================================================*/
	public function delTree( $dir ) {
		$files = array_diff( scandir( $dir ), array( '.', '..' ) );
		foreach ( $files as $file ) {
			( is_dir( "$dir/$file" ) ) ? $this->delTree( "$dir/$file" ) : @unlink( "$dir/$file" );
		}
		return @rmdir( $dir );
	}

	public function zip( $source, $destination ) {
		if ( !extension_loaded( 'zip' ) || !file_exists( $source ) ) {
			return false;
		}

		$zip = new ZipArchive();
		if ( !$zip->open( $destination, ZIPARCHIVE::CREATE ) ) {
			return false;
		}

		$source = str_replace( '\\', '/', realpath( $source ) );

		if ( is_dir( $source ) === true ) {
			$files = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $source ), RecursiveIteratorIterator::SELF_FIRST );

			foreach ( $files as $file ) {

				$file = str_replace( '\\', '/', $file );

				// Ignore "." and ".." folders
				if ( in_array( substr( $file, strrpos( $file, '/' )+1 ), array( '.', '..' ) ) )
					continue;

				$file = str_replace( '\\', '/', realpath( $file ) );

				if ( is_dir( $file ) === true ) {
					$zip->addEmptyDir( str_replace( $source . '/', '', $file . '/' ) );
				} elseif ( is_file( $file ) === true ) {
					$zip->addFromString( str_replace( $source . '/', '', $file ), file_get_contents( $file ) );
				}
			}
		} elseif ( is_file( $source ) === true ) {
			$zip->addFromString( basename( $source ), file_get_contents( $source ) );
		}
		return $zip->close();
	}
}
