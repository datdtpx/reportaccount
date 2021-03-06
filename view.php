<?php
require_once "../../config.php";
require_once "$CFG->libdir/formslib.php";
require_once "th_accountreport_form.php";
global $DB, $CFG;
global $COURSE;
if (!$course = $DB->get_record('course', array('id' => $COURSE->id))) {
	print_error('invalidcourse', 'block_th_accountreport', $COURSE->id);
}
require_login($COURSE->id);
require_capability('block/th_accountreport:view', context_course::instance($COURSE->id));
//require_capability('block/th_gradereport:view', context_course::instance($COURSE->id));
$PAGE->set_url(new moodle_url('/blocks/th_accountreport/view.php'));
$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_pagelayout('standard');
$PAGE->set_heading(get_string('namereport', 'block_th_accountreport'));
$PAGE->set_title(get_string('namereport', 'block_th_accountreport'));

echo $OUTPUT->header();

$mform = new th_accountreport_form();
$fromform = $mform->get_data();

$mform->display();

//lay ngay sau ngay ket thuc
function layngay($endday, $range) {
	$day = (new DateTime())->setTimestamp(usergetmidnight($endday));
	$day->modify($range);
	$start = $day->getTimestamp();
	return $start;
}
//lay du lieu
function account1($endday, $range) {
	global $DB;
	$day = (new DateTime())->setTimestamp(usergetmidnight($endday));
	$day->modify($range);
	$start = $day->getTimestamp();
	//echo date('d/m/Y H:i:s', $start) . ' - ' . date('d/m/Y H:i:s', $endday) . '</br>';
	return $DB->get_records_sql(
		'SELECT id,firstname,lastname FROM {user}
		WHERE deleted=0 AND timecreated>=? AND timecreated<?',
		[$start, $endday]
	);
}
//dem so tai khoan
function sotk($endday, $range) {
	global $DB;
	$day = (new DateTime())->setTimestamp(usergetmidnight($endday));
	$day->modify($range);
	$start = $day->getTimestamp();
	return $DB->count_records_sql('SELECT COUNT(id) FROM {user} WHERE deleted=0 AND timecreated>=? AND timecreated<?', [$start, $endday]);
}
//chuyen ngay kieu int sang kieu date
function day($date) {
	return date('d/m/Y', $date);
}
if ($mform->get_data() != null) {
	//ngay bat dau
	$from = $fromform->startdate;
	//ngay ket thuc
	$to = $fromform->enddate;
	if ($fromform->filter == 'day') {
		//lay du lieu sau Ngay ket thuc 1 ngay
		if (layngay($to, '-1 day') >= $from) {
			$acc = account1($to, '-1 day');
			$sotk = sotk($to, '-1 day');
		}
		//lay du lieu sau Ngay ket thuc 2 ngay
		if (layngay($to, '-2 day') >= $from) {
			$acc1 = account1(layngay($to, '-1 day'), '-1 day');
			$sotk1 = sotk(layngay($to, '-1 day'), '-1 day');
		}
		//lay du lieu sau Ngay ket thuc 3 ngay
		if (layngay($to, '-3 day') >= $from) {
			$acc2 = account1(layngay($to, '-2 day'), '-1 day');
			$sotk2 = sotk(layngay($to, '-2 day'), '-1 day');
		}
		//sau Ngay ket thuc 3 ngay
		$day = (new DateTime())->setTimestamp(usergetmidnight($to));
		$day->modify('-3 day');
		$last = $day->getTimestamp();

	} elseif ($fromform->filter == 'week') {
		//lay du lieu sau Ngay ket thuc 1 tuan
		if (layngay($to, '-1 week') >= $from) {
			$acc = account1($to, '-1 week');
			$sotk = sotk($to, '-1 week');
		}

		//lay du lieu sau Ngay ket thuc 2 tuan
		if (layngay($to, '-2 week') >= $from) {
			$acc1 = account1(layngay($to, '-1 week'), '-1 week');
			$sotk1 = sotk(layngay($to, '-1 week'), '-1 week');
		}
		//lay du lieu sau Ngay ket thuc 3 tuan
		if (layngay($to, '-3 week') >= $from) {
			$acc2 = account1(layngay($to, '-2 week'), '-1 week');
			$sotk2 = sotk(layngay($to, '-2 week'), '-1 week');
		}
		//sau Ngay ket thuc 3 tuan
		$day = (new DateTime())->setTimestamp(usergetmidnight($to));
		$day->modify('-3 week');
		$last = $day->getTimestamp();
		//Ngay ket thuc lon hon ngay bat dau nho hon 1 tuan
		if (layngay($to, '-1 week') < $from) {
			$last = $to;
		}

	} elseif ($fromform->filter == 'month') {
		//sau ngay ket thuc 1 thang
		if (layngay($to, '-1 month') >= $from) {
			$acc = account1($to, '-1 month');
			$sotk = sotk($to, '-1 month');
		}
		//sau ngay ket thuc 2 thang
		if (layngay($to, '-2 month') >= $from) {
			$acc1 = account1(layngay($to, '-1 month'), '-1 month');
			$sotk1 = sotk(layngay($to, '-1 month'), '-1 month');
		}
		//sau ngay ket thuc 3 thang
		if (layngay($to, '-3 month') >= $from) {
			$acc2 = account1(layngay($to, '-2 month'), '-1 month');
			$sotk2 = sotk(layngay($to, '-2 month'), '-1 month');
		}
		$day = (new DateTime())->setTimestamp(usergetmidnight($to));
		$day->modify('-3 month');
		$last = $day->getTimestamp();
		//Ngay ket thuc lon hon ngay bat dau nho hon 1 thang
		if (layngay($to, '-1 month') < $from) {
			$last = $to;
		}
	}

	//lay du lieu Con lai
	$accDayLeft = $DB->get_records_sql('SELECT id,firstname,lastname FROM {user} WHERE deleted=0 AND timecreated>=? AND timecreated<?', [$from, $last]);
	//dem so ban ghi
	$countAccDayLeft = $DB->count_records_sql('SELECT COUNT(id) FROM {user} WHERE deleted=0 AND timecreated>=? AND timecreated<?', [$from, $last]);
	//echo date('d/m/Y H:i:s', $from) . ' - ' . date('d/m/Y H:i:s', $last) . '</br>';
	//echo $sotk . '-' . $sotk1 . '-' . $sotk2 . '-' . $countAccDayLeft . '</br>';
	$table = new html_table();
	$table->attributes = array('class' => 'reportaccount-table');
	$lang = current_language();
	echo '<link rel="stylesheet" type="text/css" href="<https://cdn.datatables.net/1.10.21/css/jquery.dataTables.min.css">';
	$PAGE->requires->js_call_amd('local_thlib/main', 'init', array('.reportaccount-table', "reportaccount", $lang));

	$stt = 1;
	$table->head = array(get_string('id', 'block_th_accountreport'), get_string('fullname'), get_string('time'), get_string('total', 'block_th_accountreport'));

	//do du lieu ra bang
	if ($acc != null) {
		foreach ($acc as $key => $value) {
			$row = new html_table_row();
			$cell = new html_table_cell($stt);
			$row->cells[] = $cell;
			$cell = new html_table_cell(html_writer::link($CFG->wwwroot . '/user/profile.php?id=' . $value->id, $value->lastname . ' ' . $value->firstname));
			$row->cells[] = $cell;
			if ($fromform->filter == 'day' && $stt == 1) {
				$cell = new html_table_cell(day(layngay($to, '-1 day')));
				$row->cells[] = $cell;
				$cell = new html_table_cell($sotk);
			} elseif ($fromform->filter == 'week' && $stt == 1) {
				$cell = new html_table_cell(day(layngay($to, '-1 week')) . ' - ' . day($to));
				$row->cells[] = $cell;
				$cell = new html_table_cell($sotk);
			} elseif ($fromform->filter == 'month' && $stt == 1) {
				$cell = new html_table_cell(day(layngay($to, '-1 month')) . ' - ' . day($to));
				$row->cells[] = $cell;
				$cell = new html_table_cell($sotk);
			} elseif ($stt != 1) {
				$cell = new html_table_cell('');
				$row->cells[] = $cell;
				$cell = new html_table_cell('');
			}
			$row->cells[] = $cell;
			$table->data[] = $row;
			$stt++;
		}
	} else {
		$row = new html_table_row();
		$cell = new html_table_cell($stt);
		$row->cells[] = $cell;
		$cell = new html_table_cell(get_string('na', 'block_th_accountreport'));
		$row->cells[] = $cell;
		if ($fromform->filter == 'day') {
			$cell = new html_table_cell(day(layngay($to, '-1 day')));
		} elseif ($fromform->filter == 'week') {
			$cell = new html_table_cell(day(layngay($to, '-1 week')) . ' - ' . day($to));
		} elseif ($fromform->filter == 'month') {
			$cell = new html_table_cell(day(layngay($to, '-1 month')) . ' - ' . day($to));
		}
		$row->cells[] = $cell;
		$cell = new html_table_cell($sotk);
		$row->cells[] = $cell;
		$table->data[] = $row;
		$stt++;
		$sotk = 1;
	}
	if ($acc1 != null) {
		foreach ($acc1 as $key => $value) {
			$row = new html_table_row();
			$cell = new html_table_cell($stt);
			$row->cells[] = $cell;
			$cell = new html_table_cell(html_writer::link($CFG->wwwroot . '/user/profile.php?id=' . $value->id, $value->lastname . ' ' . $value->firstname));
			$row->cells[] = $cell;
			if ($fromform->filter == 'day' && $stt == $sotk + 1) {
				$cell = new html_table_cell(day(layngay($to, '-2 day')));
				$row->cells[] = $cell;
				$cell = new html_table_cell($sotk1);
			} elseif ($fromform->filter == 'week' && $stt == $sotk + 1) {
				$cell = new html_table_cell(day(layngay($to, '-2 week')) . ' - ' . day(layngay($to, '-1 week') - 86400));
				$row->cells[] = $cell;
				$cell = new html_table_cell($sotk1);
			} elseif ($fromform->filter == 'month' && $stt == $sotk + 1) {
				$cell = new html_table_cell(day(layngay($to, '-2 month')) . ' - ' . day(layngay($to, '-1 month') - 86400));
				$row->cells[] = $cell;
				$cell = new html_table_cell($sotk1);
			} elseif ($stt != $sotk + 1) {
				$cell = new html_table_cell('');
				$row->cells[] = $cell;
				$cell = new html_table_cell('');
			}
			$row->cells[] = $cell;
			$table->data[] = $row;
			$stt++;
		}
	} else {
		$row = new html_table_row();
		$cell = new html_table_cell($stt);
		$row->cells[] = $cell;
		$cell = new html_table_cell(get_string('na', 'block_th_accountreport'));
		$row->cells[] = $cell;
		if ($fromform->filter == 'day' && $stt == $sotk + 1) {
			$cell = new html_table_cell(day(layngay($to, '-2 day')));
		} elseif ($fromform->filter == 'week' && $stt == $sotk + 1) {
			$cell = new html_table_cell(day(layngay($to, '-2 week')) . ' - ' . day(layngay($to, '-1 week') - 86400));
		} elseif ($fromform->filter == 'month' && $stt == $sotk + 1) {
			$cell = new html_table_cell(day(layngay($to, '-2 month')) . ' - ' . day(layngay($to, '-1 month') - 86400));
		}
		$row->cells[] = $cell;
		$cell = new html_table_cell($sotk1);
		$row->cells[] = $cell;
		$table->data[] = $row;
		$stt++;
		$sotk1 = 1;

	}
	if ($acc2 != null) {
		foreach ($acc2 as $key => $value) {
			$row = new html_table_row();
			$cell = new html_table_cell($stt);
			$row->cells[] = $cell;
			$cell = new html_table_cell(html_writer::link($CFG->wwwroot . '/user/profile.php?id=' . $value->id, $value->lastname . ' ' . $value->firstname));
			$row->cells[] = $cell;
			if ($fromform->filter == 'day' && $stt == $sotk + $sotk1 + 1) {
				$cell = new html_table_cell(day(layngay($to, '-3 day')));
				$row->cells[] = $cell;
				$cell = new html_table_cell($sotk2);
			} elseif ($fromform->filter == 'week' && $stt == $sotk + $sotk1 + 1) {
				$cell = new html_table_cell(day(layngay($to, '-3 week')) . ' - ' . day(layngay($to, '-2 week') - 86400));
				$row->cells[] = $cell;
				$cell = new html_table_cell($sotk2);
			} elseif ($fromform->filter == 'month' && $stt == $sotk + $sotk1 + 1) {
				$cell = new html_table_cell(day(layngay($to, '-3 month')) . ' - ' . day(layngay($to, '-2 month') - 86400));
				$row->cells[] = $cell;
				$cell = new html_table_cell($sotk2);
			} elseif ($stt != $sotk + $sotk1 + 1) {
				$cell = new html_table_cell('');
				$row->cells[] = $cell;
				$cell = new html_table_cell('');
			}
			$row->cells[] = $cell;
			$table->data[] = $row;
			$stt++;
		}
	} else {
		$row = new html_table_row();
		$cell = new html_table_cell($stt);
		$row->cells[] = $cell;
		$cell = new html_table_cell(get_string('na', 'block_th_accountreport'));
		$row->cells[] = $cell;
		if ($fromform->filter == 'day' && $stt == $sotk + $sotk1 + 1) {
			$cell = new html_table_cell(day(layngay($to, '-3 day')));
		} elseif ($fromform->filter == 'week' && $stt == $sotk + $sotk1 + 1) {
			$cell = new html_table_cell(day(layngay($to, '-3 week')) . ' - ' . day(layngay($to, '-2 week') - 86400));
		} elseif ($fromform->filter == 'month' && $stt == $sotk + $sotk1 + 1) {
			$cell = new html_table_cell(day(layngay($to, '-3 month')) . ' - ' . day(layngay($to, '-2 month') - 86400));
		}
		$row->cells[] = $cell;
		$cell = new html_table_cell($sotk2);
		$row->cells[] = $cell;
		$table->data[] = $row;
		$stt++;
		$sotk2 = 1;
	}
	if ($accDayLeft != null) {
		foreach ($accDayLeft as $key => $value) {
			$row = new html_table_row();
			$cell = new html_table_cell($stt);
			$row->cells[] = $cell;
			$cell = new html_table_cell(html_writer::link($CFG->wwwroot . '/user/profile.php?id=' . $value->id, $value->lastname . ' ' . $value->firstname));
			$row->cells[] = $cell;
			if ($stt == $sotk + $sotk1 + $sotk2 + 1) {
				$cell = new html_table_cell(day($from) . ' - ' . day(layngay($last, '-1 day')));
				$row->cells[] = $cell;
				$cell = new html_table_cell($countAccDayLeft);
			} else {
				$cell = new html_table_cell('');
				$row->cells[] = $cell;
				$cell = new html_table_cell('');
			}
			$row->cells[] = $cell;
			$table->data[] = $row;
			$stt++;
		}
	} else {
		$row = new html_table_row();
		$cell = new html_table_cell($stt);
		$row->cells[] = $cell;
		$cell = new html_table_cell(get_string('na', 'block_th_accountreport'));
		$row->cells[] = $cell;
		if ($stt == $sotk + $sotk1 + $sotk2 + 1) {
			$cell = new html_table_cell(day($from) . ' - ' . day(layngay($last, '-1 day')));
		}
		$row->cells[] = $cell;
		$cell = new html_table_cell($countAccDayLeft);
		$row->cells[] = $cell;
		$table->data[] = $row;
	}
	echo html_writer::table($table);
}

// Finish the page.
echo $OUTPUT->footer();