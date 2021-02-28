<?php
class block_reportaccount extends block_base {

	public function init() {
		$this->title = get_string('namereport', 'block_reportaccount');
	}

	public function get_content() {
		if ($this->content !== null) {
			return $this->content;
		}
		global $CFG;
		$this->content = new stdClass;
		$this->content->footer = '';
		$this->content->text = html_writer::link($CFG->wwwroot . '/blocks/reportaccount/view.php', get_string('report'));
		return $this->content;
	}
}
