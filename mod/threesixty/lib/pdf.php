<?php

require_once($CFG->libdir.'/pdflib.php');

class threesixty_pdf extends pdf {

    public $footer_fullname = '';

    // Page header
    public function Header() {
        $image_file = K_PATH_IMAGES.'/mod/threesixty/pix/header_logo.png';
        $this->Image($image_file, 0, 5, 0, 10, 'PNG', '', '', 2, 150, 'R', false, false, 0, false, false, false, false, array());
    }

    // Page footer
    public function Footer() {
        $this->SetY(-12);
        $this->SetFont('helvetica', '', 8);
        $a = new stdClass();
        $a->fullname = $this->footer_fullname;
        // Fudge these to improve spacing
        $this->alias_tot_pages = '{t}';
        $this->alias_num_page = '{n}';
        $a->x = $this->getAliasNumPage();
        $a->y = $this->getAliasNbPages();
        $this->Cell(0, 0, get_string('pdf:footer:line1', 'threesixty', $a), 0, 1, 'C', false, '', 0, false, 'T', 'M');
        $line2 = html_entity_decode(get_string('pdf:footer:line2', 'threesixty'), ENT_QUOTES, 'UTF-8');
        $this->Cell(0, 0, $line2, 0, 0, 'C', false, '', 0, false, 'T', 'M');
    }
}
