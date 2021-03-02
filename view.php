<?php
require_once "../../config.php";
require_once "$CFG->libdir/formslib.php";
require_once "lib.php";
global $DB, $CFG;

require_login();

$PAGE->set_url(new moodle_url('/blocks/reportaccount/view.php'));
$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_pagelayout('report');
$PAGE->set_heading(get_string('namereport', 'block_reportaccount'));
$PAGE->set_title(get_string('namereport', 'block_reportaccount'));

echo $OUTPUT->header();

$mform = new reportaccount_form();
$fromform = $mform->get_data();

$mform->display();

if ($mform->get_data() != null) {
	//ngay bat dau
	$from = $fromform->startdate;
	//ngay ket thuc
	$to = $fromform->enddate;
	if ($fromform->filter == 'day') {
		$day = (new DateTime())->setTimestamp(usergetmidnight($to));
		$last = $day->getTimestamp();

		//lay du lieu Ngay
		$acc = $DB->get_records_sql('SELECT id,firstname,lastname FROM {user} WHERE timecreated>=? AND timecreated<?', [$to, $to + 86400]);
		$countAcc = $DB->count_records_sql('SELECT COUNT(id) FROM {user} WHERE timecreated>=? AND timecreated<?', [$to, $to + 86400]);

	} elseif ($fromform->filter == 'week') {
		//sau ngay ket thuc 1 tuan
		$day = (new DateTime())->setTimestamp(usergetmidnight($to));
		$day->modify('-1 week');
		$last = $day->getTimestamp();

		//lay du lieu Tuan
		$acc = $DB->get_records_sql('SELECT id,firstname,lastname FROM {user} WHERE timecreated>=? AND timecreated<=?', [$last, $to + 86400]);
		$countAcc = $DB->count_records_sql('SELECT COUNT(id) FROM {user} WHERE timecreated>=? AND timecreated<=?', [$last, $to + 86400]);

	} elseif ($fromform->filter == 'month') {
		//sau ngay ket thuc 1 thang
		$day = (new DateTime())->setTimestamp(usergetmidnight($to));
		$day->modify('-1 month');
		$last = $day->getTimestamp();

		//lay du lieu Thang
		$acc = $DB->get_records_sql('SELECT id,firstname,lastname FROM {user} WHERE timecreated>=? AND timecreated<=?', [$last, $to + 86400]);
		$countAcc = $DB->count_records_sql('SELECT COUNT(id) FROM {user} WHERE timecreated>=? AND timecreated<=?', [$last, $to + 86400]);
	}

	//lay du lieu Con lai
	$accDayLeft = $DB->get_records_sql('SELECT id,firstname,lastname FROM {user} WHERE timecreated>=? AND timecreated<?', [$from, $last]);
	$countAccDayLeft = $DB->count_records_sql('SELECT COUNT(id) FROM {user} WHERE timecreated>=? AND timecreated<?', [$from, $last]);

	$table = new html_table();
	$stt = 1;
	$table->head = array(get_string('id', 'block_reportaccount'), get_string('fullname'), get_string('time'));

	//do du lieu ra bang
	foreach ($acc as $key => $value) {
		$row = new html_table_row();
		$cell = new html_table_cell($stt++);
		$row->cells[] = $cell;
		$cell = new html_table_cell(html_writer::link($CFG->wwwroot . '/user/profile.php?id=' . $value->id, $value->lastname . ' ' . $value->firstname));
		$row->cells[] = $cell;
		if ($fromform->filter == 'day') {
			$cell = new html_table_cell(date("d/m/Y", $to));
		} else {
			$cell = new html_table_cell(date("d/m/Y", $last) . ' - ' . date("d/m/Y", $to));
		}
		$cell->rowspan = $countAcc;
		$row->cells[] = $cell;
		$table->data[] = $row;
		break;
	}
	array_shift($acc);
	foreach ($acc as $key => $value) {
		$row = new html_table_row();
		$cell = new html_table_cell($stt++);
		$row->cells[] = $cell;
		$cell = new html_table_cell(html_writer::link($CFG->wwwroot . '/user/profile.php?id=' . $value->id, $value->lastname . ' ' . $value->firstname));
		$row->cells[] = $cell;
		$table->data[] = $row;
	}

	//do du lieu Con lai ra bang
	foreach ($accDayLeft as $key => $value) {
		$row = new html_table_row();
		$cell = new html_table_cell($stt++);
		$row->cells[] = $cell;
		$cell = new html_table_cell(html_writer::link($CFG->wwwroot . '/user/profile.php?id=' . $value->id, $value->lastname . ' ' . $value->firstname));
		$row->cells[] = $cell;
		$cell = new html_table_cell(date("d/m/Y", $from) . ' - ' . date("d/m/Y", $last - 86400));
		$cell->rowspan = $countAccDayLeft;
		$row->cells[] = $cell;
		$table->data[] = $row;
		break;
	}
	array_shift($accDayLeft);
	foreach ($accDayLeft as $key => $value) {
		$row = new html_table_row();
		$cell = new html_table_cell($stt++);
		$row->cells[] = $cell;
		$cell = new html_table_cell(html_writer::link($CFG->wwwroot . '/user/profile.php?id=' . $value->id, $value->lastname . ' ' . $value->firstname));
		$row->cells[] = $cell;
		$table->data[] = $row;
	}

	echo html_writer::table($table);
}

// Finish the page.
echo $OUTPUT->footer();