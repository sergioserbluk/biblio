<?php
/*******************************************************************************
* FPDF                                                                         *
*                                                                              *
* Version: 1.82                                                                *
* Date:    2019-11-30                                                          *
* Author:  Olivier PLATHEY                                                     *
*******************************************************************************/

class FPDF
{
    protected $page;               // current page number
    protected $n;                  // current object number
    protected $offsets;            // array of object offsets
    protected $buffer;             // buffer holding in-memory PDF
    protected $pages;              // array containing pages
    protected $state;              // current document state
    protected $compress;           // compression flag
    protected $k;                  // scale factor (number of points in user unit)
    protected $DefOrientation;     // default orientation
    protected $CurOrientation;     // current orientation
    protected $OrientationChanges; // array indicating orientation changes
    protected $fwPt, $fhPt;        // dimensions of page format in points
    protected $fw, $fh;            // dimensions of page format in user unit
    protected $wPt, $hPt;          // current dimensions of page in points
    protected $w, $h;              // current dimensions of page in user unit
    protected $lMargin;            // left margin
    protected $tMargin;            // top margin
    protected $rMargin;            // right margin
    protected $bMargin;            // page break margin
    protected $cMargin;            // cell margin
    protected $x, $y;              // current position in user unit
    protected $lasth;              // height of last printed cell
    protected $LineWidth;          // line width in user unit
    protected $fontpath;           // path containing fonts
    protected $CoreFonts;          // array of core font names
    protected $fonts;              // array of used fonts
    protected $FontFiles;          // array of font files
    protected $encodings;          // array of encodings
    protected $cmaps;              // array of ToUnicode CMaps
    protected $FontFamily;         // current font family
    protected $FontStyle;          // current font style
    protected $underline;          // underlining flag
    protected $CurrentFont;        // current font info
    protected $FontSizePt;         // current font size in points
    protected $FontSize;           // current font size in user unit
    protected $DrawColor;          // commands for drawing color
    protected $FillColor;          // commands for filling color
    protected $TextColor;          // commands for text color
    protected $ColorFlag;          // indicates whether fill and text colors are different
    protected $ws;                 // word spacing
    protected $images;             // array of used images
    protected $PageLinks;          // array of links in pages
    protected $links;              // array of internal links
    protected $AutoPageBreak;      // automatic page breaking
    protected $PageBreakTrigger;   // threshold used to trigger page breaks
    protected $InHeader;           // flag set when processing header
    protected $InFooter;           // flag set when processing footer
    protected $ZoomMode;           // zoom display mode
    protected $LayoutMode;         // layout display mode
    protected $title;              // title
    protected $subject;            // subject
    protected $author;             // author
    protected $keywords;           // keywords
    protected $creator;            // creator
    protected $AliasNbPages;       // alias for total number of pages
    protected $PDFVersion;         // PDF version number

    // Constructor
    function __construct($orientation='P', $unit='mm', $size='A4')
    {
        $this->page = 0;
        $this->n = 2;
        $this->buffer = '';
        $this->pages = array();
        $this->OrientationChanges = array();
        $this->state = 0;
        $this->fonts = array();
        $this->FontFiles = array();
        $this->encodings = array();
        $this->cmaps = array();
        $this->images = array();
        $this->links = array();
        $this->InHeader = false;
        $this->InFooter = false;
        $this->lasth = 0;
        $this->FontFamily = '';
        $this->FontStyle = '';
        $this->FontSizePt = 12;
        $this->underline = false;
        $this->DrawColor = '0 G';
        $this->FillColor = '0 g';
        $this->TextColor = '0 g';
        $this->ColorFlag = false;
        $this->ws = 0;
        $this->FontFiles = array();
        $this->fonts = array();
        $this->CoreFonts = array('courier','helvetica','times','symbol','zapfdingbats');

        // Set scale factor
        if($unit=='pt')
            $this->k = 1;
        elseif($unit=='mm')
            $this->k = 72/25.4;
        elseif($unit=='cm')
            $this->k = 72/2.54;
        elseif($unit=='in')
            $this->k = 72;
        else
            $this->Error('Incorrect unit: '.$unit);

        // Page format
        if(is_string($size))
        {
            $size = strtolower($size);
            $a = array(
                'a3'=>array(841.89,1190.55),
                'a4'=>array(595.28,841.89),
                'a5'=>array(420.94,595.28),
                'letter'=>array(612,792),
                'legal'=>array(612,1008)
            );
            if(!isset($a[$size]))
                $this->Error('Unknown page size: '.$size);
            $this->fwPt = $a[$size][0];
            $this->fhPt = $a[$size][1];
        }
        else
        {
            $this->fwPt = $size[0]*$this->k;
            $this->fhPt = $size[1]*$this->k;
        }
        $this->fw = $this->fwPt/$this->k;
        $this->fh = $this->fhPt/$this->k;

        // Page orientation
        $orientation = strtolower($orientation);
        if($orientation=='p' || $orientation=='portrait')
        {
            $this->DefOrientation = 'P';
            $this->wPt = $this->fwPt;
            $this->hPt = $this->fhPt;
        }
        elseif($orientation=='l' || $orientation=='landscape')
        {
            $this->DefOrientation = 'L';
            $this->wPt = $this->fhPt;
            $this->hPt = $this->fwPt;
        }
        else
            $this->Error('Incorrect orientation: '.$orientation);
        $this->CurOrientation = $this->DefOrientation;
        $this->CurOrientation = $this->DefOrientation;
        $this->SetMargins(10,10);
        $this->cMargin = 2;
        $this->LineWidth = .567/$this->k;
        $this->SetAutoPageBreak(true, 10);
        $this->SetDisplayMode('default');
        $this->compress = false;
        $this->PDFVersion = '1.3';
    }

    function Error($msg)
    {
        throw new Exception('FPDF error: '.$msg);
    }

    // AddPage, Output, and SetFont are the key methods:
    function AddPage()
    {
        $this->page++;
        $this->pages[$this->page] = '';
    }

    function SetFont($family, $style='', $size=0)
    {
        $family = strtolower($family);
        if(!in_array($family, $this->CoreFonts))
            $this->Error('Undefined font: '.$family.' '.$style);
        $this->FontFamily = $family;
        $this->FontStyle = $style;
        $this->FontSizePt = $size;
    }

    function Output($dest='', $name='', $isUTF8=false)
    {
        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="'.basename($name).'"');
        echo "%PDF-".$this->PDFVersion."\n%EOF";
    }
}
?>
