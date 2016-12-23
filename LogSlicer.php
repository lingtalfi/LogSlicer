<?php


namespace LogSlicer;


use Bat\FileTool;


/**
 * Note: this implementation use array_reverse for reverse mode,
 * so it's not very efficient, hence choose the nbItemsPerPage wisely.
 *
 * The default of 1000 seems a reasonable starting point for me.
 *
 */
class LogSlicer
{

    private $_file;
    private $_nbLinesPerPage;
    private $_reverse;

    public function __construct()
    {
        $this->_nbLinesPerPage = 1000;
        $this->_reverse = false;
    }

    public static function create()
    {
        return new self();
    }

    public function file($file)
    {
        $this->_file = $file;
        return $this;
    }

    public function reverse()
    {
        $this->_reverse = true;
        return $this;
    }

    public function nbLinesPerPage($n)
    {
        $this->_nbLinesPerPage = $n;
        return $this;
    }

    public function getPage($n)
    {

        $nbLines = FileTool::getNbLines($this->_file);
        $nbPages = ceil($nbLines / $this->_nbLinesPerPage);
        if ($n < 1) {
            $n = 1;
        }
        if ($n > $nbPages) {
            $n = $nbPages;
        }
        if (false === $this->_reverse) {
            $offset = ($n - 1) * $this->_nbLinesPerPage;
            $end = $offset + $this->_nbLinesPerPage - 1;
        } else {
            $end = $nbLines - (($n - 1) * $this->_nbLinesPerPage) - 1;
            $offset = $end - $this->_nbLinesPerPage + 1;
        }


        $handle = fopen($this->_file, "r");
        $linecount = 0;
        $lines = [];


        while (!feof($handle)) {
            $line = fgets($handle);
            if ($linecount >= $offset) {
                $lines[] = trim($line);
            }
            $linecount++;
            if ($linecount > $end) {
                break;
            }
        }
        fclose($handle);
        if (true === $this->_reverse) {
            $lines = array_reverse($lines);
        }
        return $lines;
    }


    public function getPageForward($n)
    {

        $nbLines = FileTool::getNbLines($this->_file);
        $nbPages = ceil($nbLines / $this->_nbLinesPerPage);
        if ($n < 1) {
            $n = 1;
        }
        if ($n > $nbPages) {
            $n = $nbPages;
        }

        $offset = ($n - 1) * $this->_nbLinesPerPage;
        $handle = fopen($this->_file, "r");
        $linecount = 0;
        $g = 1;
        ob_start();
        while (!feof($handle)) {
            $line = fgets($handle);
            if ($linecount >= $offset) {
                echo $line;
                $g++;
                if ($g > $this->_nbLinesPerPage) {
                    break;
                }
            }
            $linecount++;
        }
        fclose($handle);
        return trim(ob_get_clean());
    }
}