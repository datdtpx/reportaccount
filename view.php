<?php
require_once "../../config.php";
require_once "$CFG->libdir/formslib.php";
require_once "lib.php";
global $DB;

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

	//ngay ket thuc
	$to = $fromform->enddate;

	//sau ngay ket thuc 1 ngay
	$day = (new DateTime())->setTimestamp(usergetmidnight($fromform->enddate));
	$day->modify('-1 day');
	$lastDay = $day->getTimestamp();

	//sau ngay ket thuc 1 ngay 1 tuan
	$day->modify('-1 week');
	$lastWeek1 = $day->getTimestamp();

	//sau ngay ket thuc 2 ngay 1 tuan
	$day->modify('-1 day');
	$lastWeek2 = $day->getTimestamp();

	//sau ngay ket thuc 3 ngay 1 tuan 1 thang
	$day->modify('-1 day');
	$day->modify('-1 month');
	$lastMonth1 = $day->getTimestamp();

	//sau ngay ket thuc 4 ngay 1 tuan 1 thang
	$day->modify('-1 day');
	$lastMonth2 = $day->getTimestamp();

	//ngay bat dau
	$from = $fromform->startdate;

	//lay du lieu Ngay
	$accDay = $DB->get_records_sql('SELECT username,firstname,lastname FROM {user} WHERE timecreated>=?', [$to]);
	$countAccDay = $DB->count_records_sql('SELECT COUNT(id) FROM {user} WHERE timecreated>=?', [$to]);

	//lay du lieu Tuan
	$accWeek = $DB->get_records_sql('SELECT username,firstname,lastname FROM {user} WHERE timecreated>=? AND timecreated<=?', [$lastWeek1, $lastDay]);
	$countAccWeek = $DB->count_records_sql('SELECT COUNT(id) FROM {user} WHERE timecreated>=? AND timecreated<=?', [$lastWeek1, $lastDay]);

	//lay du lieu Thang
	$accMonth = $DB->get_records_sql('SELECT username,firstname,lastname FROM {user} WHERE timecreated>=? AND timecreated<=?', [$lastMonth1, $lastWeek2]);
	$countAccMonth = $DB->count_records_sql('SELECT COUNT(id) FROM {user} WHERE timecreated>=? AND timecreated<=?', [$lastMonth1, $lastWeek2]);

	//lay du lieu Con lai
	$accDayLeft = $DB->get_records_sql('SELECT username,firstname,lastname FROM {user} WHERE timecreated>=? AND timecreated<=?', [$from, $lastMonth2]);
	$countAccDayLeft = $DB->count_records_sql('SELECT COUNT(id) FROM {user} WHERE timecreated>=? AND timecreated<=?', [$from, $lastMonth2]);

	$table = new html_table();
	$stt = 1;
	$table->head = array(get_string('id', 'block_reportaccount'), get_string('fullname'), get_string('time'));

	//do du lieu Ngay ra bang
	foreach ($accDay as $key => $value) {
		$row = new html_table_row();
		$cell = new html_table_cell($stt++);
		$row->cells[] = $cell;
		$cell = new html_table_cell($value->lastname . ' ' . $value->firstname);
		$row->cells[] = $cell;
		$cell = new html_table_cell(date("d/m/Y", $to));
		$cell->rowspan = $countAccDay;
		$row->cells[] = $cell;
		$table->data[] = $row;
		break;
	}
	array_shift($accDay);
	foreach ($accDay as $key => $value) {
		$row = new html_table_row();
		$cell = new html_table_cell($stt++);
		$row->cells[] = $cell;
		$cell = new html_table_cell($value->lastname . ' ' . $value->firstname);
		$row->cells[] = $cell;
		$table->data[] = $row;
	}

	//do du lieu Tuan ra bang
	foreach ($accWeek as $key => $value) {
		$row = new html_table_row();
		$cell = new html_table_cell($stt++);
		$row->cells[] = $cell;
		$cell = new html_table_cell($value->lastname . ' ' . $value->firstname);
		$row->cells[] = $cell;
		$cell = new html_table_cell(date("d/m/Y", $lastWeek1) . ' - ' . date("d/m/Y", $lastDay));
		$cell->rowspan = $countAccWeek;
		$row->cells[] = $cell;
		$table->data[] = $row;
		break;
	}
	array_shift($accWeek);
	foreach ($accWeek as $key => $value) {
		$row = new html_table_row();
		$cell = new html_table_cell($stt++);
		$row->cells[] = $cell;
		$cell = new html_table_cell($value->lastname . ' ' . $value->firstname);
		$row->cells[] = $cell;
		$table->data[] = $row;
	}

	//do du lieu Thang ra bang
	foreach ($accMonth as $key => $value) {
		$row = new html_table_row();
		$cell = new html_table_cell($stt++);
		$row->cells[] = $cell;
		$cell = new html_table_cell($value->lastname . ' ' . $value->firstname);
		$row->cells[] = $cell;
		$cell = new html_table_cell(date("d/m/Y", $lastMonth1) . ' - ' . date("d/m/Y", $lastWeek2));
		$cell->rowspan = $countAccMonth;
		$row->cells[] = $cell;
		$table->data[] = $row;
		break;
	}
	array_shift($accMonth);
	foreach ($accMonth as $key => $value) {
		$row = new html_table_row();
		$cell = new html_table_cell($stt++);
		$row->cells[] = $cell;
		$cell = new html_table_cell($value->lastname . ' ' . $value->firstname);
		$row->cells[] = $cell;
		$table->data[] = $row;
	}

	//do du lieu Con lai ra bang
	foreach ($accDayLeft as $key => $value) {
		$row = new html_table_row();
		$cell = new html_table_cell($stt++);
		$row->cells[] = $cell;
		$cell = new html_table_cell($value->lastname . ' ' . $value->firstname);
		$row->cells[] = $cell;
		$cell = new html_table_cell(date("d/m/Y", $from) . ' - ' . date("d/m/Y", $lastMonth2));
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
		$cell = new html_table_cell($value->lastname . ' ' . $value->firstname);
		$row->cells[] = $cell;
		$table->data[] = $row;
	}

	echo html_writer::table($table);
}

// Finish the page.
echo $OUTPUT->footer();