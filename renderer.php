<?php
/**
 * DokuWiki Plugin simplexml (Renderer Component)
 *
 * @author   Patrick Bueker <Patrick@patrickbueker.de>
 * @license  GPLv2 or later (http://www.gnu.org/licenses/gpl.html)
 * @version  $Rev: 12 $
 *
 */

// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

if (!defined('DOKU_LF')) define('DOKU_LF', "\n");
if (!defined('DOKU_TAB')) define('DOKU_TAB', "\t");
require_once DOKU_INC . 'inc/parser/renderer.php';

/**
 *  Renderer for simple XML is a simple renderer to
 *  render DokuWiki as XML. It uses XML elements mostly
 *  the way the DokuWiki renderer works internally.
 *  Be aware that the output may not be sanitized,
 *  so be careful.
 */
class renderer_plugin_simplexml extends Doku_Renderer {
    var $info = array(
        'cache' => false, // may the rendered result cached?
        'toc'   => false, // render the TOC?
    );
    var $precedinglevel = array();
    var $nextHeader     = "";

    function nocache() {
        $this->info['cache'] = false;
    }

    function notoc() {
        $this->info['toc'] = false;
    }

    /**
     * Returns the format produced by this renderer.
     *
     * Has to be overidden by decendend classes
     */

    function getFormat(){
        return 'simplexml';
    }

    /**
     * Return some info for the dokuwiki plugin manager.
     */
    function getInfo(){
        $ver = '$Date: 2010-03-15 21:01:52 +0100 (Mo, 15. Mär 2010) $';
        $ver = substr($ver, 7, 10);
        return array(
            'base'   => 'simplexml',
            'author' => 'Patrick',
            'email'  => 'Patrick@PatrickBueker.de',
            'date'   => "$ver",
            'name'   => 'Simple XML Renderer Plugin',
            'desc'   => 'Renders dokuwiki as simple XML output. Read comments in source.',
            'url'    => 'http://www.patrickbueker.de/dokuwiki/simplexml.tgz',
        );
    }

    function document_start() {
        global $ID;
        global $INFO;
        $this->doc  = '<?xml version="1.0" encoding="UTF-8"?>'.DOKU_LF;
        $this->doc .= '<document namespace="' . $INFO['namespace'] . '"  revision="' . $INFO['rev'] . '" id="' . cleanID($ID) . '" lastmod="' . $INFO['lastmod'] . '">'.DOKU_LF;
    }

    function document_end() {
        while(count($this->precedinglevel)>0)
        {
            $this->doc .= '</section>'.DOKU_LF;
            $this->doc .= '<!--' . array_pop($this->precedinglevel) .  '-->'.DOKU_LF;
        }
        $this->doc .= '</document>'.DOKU_LF;
    }

    function render_TOC() {
        $this->doc .= '<render_TOC/>'.DOKU_LF;
    }

    function toc_additem($id, $text, $level) {}

    function header($text, $level, $pos) {
        $this->nextHeader  = '<header level="' . $level . '" pos="' . $pos . '">'.DOKU_LF;
        $this->nextHeader .= htmlspecialchars($text,ENT_COMPAT,'UTF-8',false);
        $this->nextHeader .= '</header>'.DOKU_LF;
    }

    function section_edit($start, $end, $level, $name) {
            $this->doc .= '<section_edit start="' . $start . '" end="' . $end . '" level="' . $level . '" name="' . $name . '"/>'.DOKU_LF;
    }

    function section_open($level) {
        while(end($this->precedinglevel) >= $level)
        {
            $this->doc .= '</section>'.DOKU_LF;
            $this->doc .= '<!--' . array_pop($this->precedinglevel) .  '-->'.DOKU_LF;
        }

        $this->doc .= '<section level="' . $level . '" >'.DOKU_LF;
        $this->doc .= $this->nextHeader;
	$this->nextHeader = "";
        array_push($this->precedinglevel,$level);
    }

    function section_close() {
        #$this->doc .= '</section>'.DOKU_LF;
    }

    function cdata($text) {
        $this->doc .= htmlspecialchars($text,ENT_COMPAT,'UTF-8',false);
    }

    function p_open() {
        $this->doc .= '<p>'.DOKU_LF;
    }

    function p_close() {
        $this->doc .= '</p>'.DOKU_LF;
    }

    function linebreak() {
        $this->doc .= '<linebreak/>'.DOKU_LF;
    }

    function hr() {
        $this->doc .= '<hr/>'.DOKU_LF;
    }

    function strong_open() {
        $this->doc .= '<strong>'.DOKU_LF;
    }

    function strong_close() {
        $this->doc .= '</strong>'.DOKU_LF;
    }

    function emphasis_open() {
        $this->doc .= '<emphasis>'.DOKU_LF;
    }

    function emphasis_close() {
        $this->doc .= '</emphasis>'.DOKU_LF;
    }

    function underline_open() {
        $this->doc .= '<underline>'.DOKU_LF;
    }

    function underline_close() {
        $this->doc .= '</underline>'.DOKU_LF;
    }

    function monospace_open() {
        $this->doc .= '<monospace>'.DOKU_LF;
    }

    function monospace_close() {
        $this->doc .= '</monospace>'.DOKU_LF;
    }

    function subscript_open() {
        $this->doc .= '<subscript>'.DOKU_LF;
    }

    function subscript_close() {
        $this->doc .= '</subscript>'.DOKU_LF;
    }

    function superscript_open() {
        $this->doc .= '<superscript>'.DOKU_LF;
    }

    function superscript_close() {
        $this->doc .= '</superscript>'.DOKU_LF;
    }

    function deleted_open() {
        $this->doc .= '<deleted>'.DOKU_LF;
    }

    function deleted_close() {
        $this->doc .= '</deleted>'.DOKU_LF;
    }

    function footnote_open() {
        $this->doc .= '<footnote>'.DOKU_LF;
    }

    function footnote_close() {
        $this->doc .= '</footnote>'.DOKU_LF;
    }

    function listu_open() {
        $this->doc .= '<listu>'.DOKU_LF;
    }

    function listu_close() {
        $this->doc .= '</listu>'.DOKU_LF;
    }

    function listo_open() {
        $this->doc .= '<listo>'.DOKU_LF;
    }

    function listo_close() {
        $this->doc .= '</listo>'.DOKU_LF;
    }

    function listitem_open($level) {
        $this->doc .= '<listitem level="' . $level . '">'.DOKU_LF;
    }

    function listitem_close() {
        $this->doc .= '</listitem>'.DOKU_LF;
    }

    function listcontent_open() {
        $this->doc .= '<listcontent>'.DOKU_LF;
    }

    function listcontent_close() {
        $this->doc .= '</listcontent>'.DOKU_LF;
    }

    function unformatted($text) {
        $this->doc .= '<unformatted>'.DOKU_LF;
        $this->doc .= htmlspecialchars($text,ENT_COMPAT,'UTF-8',false);
        $this->doc .= '</unformatted>'.DOKU_LF;
    }

    function php($text) {
        $this->doc .= '</php>'.DOKU_LF;
        $this->doc .= htmlspecialchars($text,ENT_COMPAT,'UTF-8',false);
        $this->doc .= '</php>'.DOKU_LF;
    }

    function html($text) {
        $this->doc .= '<html>'.DOKU_LF;
        $this->doc .= htmlspecialchars($text,ENT_COMPAT,'UTF-8',false);
        $this->doc .= '</html>'.DOKU_LF;
    }

    function preformatted($text) {
        $this->doc .= '<preformatted>'.DOKU_LF;
        $this->doc .= htmlspecialchars($text,ENT_COMPAT,'UTF-8',false);
        $this->doc .= '</preformatted>'.DOKU_LF;
    }

    function file($text) {
        $this->doc .= '<file>'.DOKU_LF;
        $this->doc .= htmlspecialchars($text,ENT_COMPAT,'UTF-8',false);
        $this->doc .= '</file>'.DOKU_LF;
    }

    function quote_open() {
        $this->doc .= '<quote>'.DOKU_LF;
    }

    function quote_close() {
        $this->doc .= '</quote>'.DOKU_LF;
    }

    function code($text, $lang = NULL) {
        $this->doc .= '<code lang="' . $lang . '">'.DOKU_LF;
        $this->doc .= htmlspecialchars($text,ENT_COMPAT,'UTF-8',false);
        $this->doc .= '</code>'.DOKU_LF;
    }

    function acronym($acronym) {
        $this->doc .= '<acronym>'.DOKU_LF;
        $this->doc .= htmlspecialchars($acronym,ENT_COMPAT,'UTF-8',false);
        $this->doc .= '</acronym>'.DOKU_LF;
    }

    function smiley($smiley) {
        $this->doc .= '<smiley>'.DOKU_LF;
        $this->doc .= htmlspecialchars($smiley,ENT_COMPAT,'UTF-8',false);
        $this->doc .= '</smiley>'.DOKU_LF;
    }

    function wordblock($word) {
        $this->doc .= '<wordblock>'.DOKU_LF;
        $this->doc .= htmlspecialchars($word,ENT_COMPAT,'UTF-8',false);
        $this->doc .= '</wordblock>'.DOKU_LF;
    }

    function entity($entity) {
        $this->doc .= '<entity>'.DOKU_LF;
        $this->doc .= htmlspecialchars($entety,ENT_COMPAT,'UTF-8',false);
        $this->doc .= '</entity>'.DOKU_LF;
    }

    // 640x480 ($x=640, $y=480)
    function multiplyentity($x, $y) {
        $this->doc .= '<multiplyentity>'.DOKU_LF;
        $this->doc .= '<x>'.DOKU_LF;
        $this->doc .= htmlspecialchars($x,ENT_COMPAT,'UTF-8',false);
        $this->doc .= '</x>'.DOKU_LF;
        $this->doc .= '<y>'.DOKU_LF;
        $this->doc .= htmlspecialchars($y,ENT_COMPAT,'UTF-8',false);
        $this->doc .= '</y>'.DOKU_LF;
        $this->doc .= '</multiplyentity>'.DOKU_LF;
    }

    function singlequoteopening() {
        $this->doc .= '<singlequote>'.DOKU_LF;
    }

    function singlequoteclosing() {
        $this->doc .= '</singlequote>'.DOKU_LF;
    }

    function apostrophe() {
        $this->doc .= '<apostrophe/>'.DOKU_LF;
    }

    function doublequoteopening() {
        $this->doc .= '<doublequote>'.DOKU_LF;
    }

    function doublequoteclosing() {
        $this->doc .= '</doublequote>'.DOKU_LF;
    }

    // $link like 'SomePage'
    function camelcaselink($link) {
        $this->doc .= '<camelcaselink>'.DOKU_LF;
        $this->doc .= htmlspecialchars($link,ENT_COMPAT,'UTF-8',false);
        $this->doc .= '</camelcaselink>'.DOKU_LF;
    }

    function locallink($hash, $name = NULL) {
        $this->doc .= '<locallink>'.DOKU_LF;
        $this->doc .= '<hash>'.DOKU_LF;
        $this->doc .= htmlspecialchars($hash,ENT_COMPAT,'UTF-8',false);
        $this->doc .= '</hash>'.DOKU_LF;
        $this->doc .= '<name>'.DOKU_LF;
        $this->doc .= htmlspecialchars($name,ENT_COMPAT,'UTF-8',false);
        $this->doc .= '</name>'.DOKU_LF;
        $this->doc .= '</locallink>'.DOKU_LF;
    }

    // $link like 'wiki:syntax', $title could be an array (media)
    function internallink($link, $title = NULL) {
        $this->doc .= '<internallink>'.DOKU_LF;
        $this->doc .= '<link>'.DOKU_LF;
        $this->doc .= htmlspecialchars($link,ENT_COMPAT,'UTF-8',false);
        $this->doc .= '</link>'.DOKU_LF;
        $this->doc .= '<title>'.DOKU_LF;
        $this->doc .= htmlspecialchars($title,ENT_COMPAT,'UTF-8',false);
        $this->doc .= '</title>'.DOKU_LF;
        $this->doc .= '</internallink>'.DOKU_LF;
    }

    // $link is full URL with scheme, $title could be an array (media)
    function externallink($link, $title = NULL) {
        $this->doc .= '<externallink>'.DOKU_LF;
        $this->doc .= '<link>'.DOKU_LF;
        $this->doc .= htmlspecialchars($link,ENT_COMPAT,'UTF-8',false);
        $this->doc .= '</link>'.DOKU_LF;
        $this->doc .= '<title>'.DOKU_LF;
        $this->doc .= htmlspecialchars($title,ENT_COMPAT,'UTF-8',false); // FIXME: could be an array
        $this->doc .= '</title>'.DOKU_LF;
        $this->doc .= '</externallink>'.DOKU_LF;
    }

    // $link is the original link - probably not much use
    // $wikiName is an indentifier for the wiki
    // $wikiUri is the URL fragment to append to some known URL
    function interwikilink($link, $title = NULL, $wikiName, $wikiUri) {
        $this->doc .= '<interwikilink>'.DOKU_LF;
        $this->doc .= '<link>'.DOKU_LF;
        $this->doc .= htmlspecialchars($link,ENT_COMPAT,'UTF-8',false);
        $this->doc .= '</link>'.DOKU_LF;
        $this->doc .= '<title>'.DOKU_LF;
        $this->doc .= htmlspecialchars($title,ENT_COMPAT,'UTF-8',false);
        $this->doc .= '</title>'.DOKU_LF;
        $this->doc .= '<wikiName>'.DOKU_LF;
        $this->doc .= htmlspecialchars($wikiName,ENT_COMPAT,'UTF-8',false);
        $this->doc .= '</wikiName>'.DOKU_LF;
        $this->doc .= '<wikiUri>'.DOKU_LF;
        $this->doc .= htmlspecialchars($wikiUri,ENT_COMPAT,'UTF-8',false);
        $this->doc .= '</wikiUri>'.DOKU_LF;
        $this->doc .= '</interwikilink>'.DOKU_LF;
    }

    // Link to file on users OS, $title could be an array (media)
    function filelink($link, $title = NULL) {
        $this->doc .= '<filelink>'.DOKU_LF;
        $this->doc .= '<link>'.DOKU_LF;
        $this->doc .= htmlspecialchars($link,ENT_COMPAT,'UTF-8',false);
        $this->doc .= '</link>'.DOKU_LF;
        $this->doc .= '<title>'.DOKU_LF;
        $this->doc .= htmlspecialchars($title,ENT_COMPAT,'UTF-8',false);
        $this->doc .= '</title>'.DOKU_LF;
        $this->doc .= '</filelink>'.DOKU_LF;
    }

    // Link to a Windows share, , $title could be an array (media)
    function windowssharelink($link, $title = NULL) {
        $this->doc .= '<windowssharelink>'.DOKU_LF;
        $this->doc .= '<link>'.DOKU_LF;
        $this->doc .= htmlspecialchars($link,ENT_COMPAT,'UTF-8',false);
        $this->doc .= '</link>'.DOKU_LF;
        $this->doc .= '<title>'.DOKU_LF;
        $this->doc .= htmlspecialchars($title,ENT_COMPAT,'UTF-8',false);
        $this->doc .= '</title>'.DOKU_LF;
        $this->doc .= '</windowssharelink>'.DOKU_LF;
    }

//  function email($address, $title = NULL) {}
    function emaillink($address, $name = NULL) {
        $this->doc .= '<emaillink>'.DOKU_LF;
        $this->doc .= '<address>'.DOKU_LF;
        $this->doc .= htmlspecialchars($address,ENT_COMPAT,'UTF-8',false);
        $this->doc .= '</address>'.DOKU_LF;
        $this->doc .= '<name>'.DOKU_LF;
        $this->doc .= htmlspecialchars($name,ENT_COMPAT,'UTF-8',false);
        $this->doc .= '</name>'.DOKU_LF;
        $this->doc .= '</emaillink>'.DOKU_LF;
}

    function internalmedia ($src, $title=NULL, $align=NULL, $width=NULL,
                            $height=NULL, $cache=NULL, $linking=NULL) {
        $this->doc .= '<internalmedia align="' . $align . '" width="' . $width . '" height="' . $height . '" cache="' . $cache . '" linking="' . $linking . '">'.DOKU_LF;
        $this->doc .= '<src>'.DOKU_LF;
        $this->doc .= htmlspecialchars($src,ENT_COMPAT,'UTF-8',false);
        $this->doc .= '</src>'.DOKU_LF;
        $this->doc .= '<title>'.DOKU_LF;
        $this->doc .= htmlspecialchars($title,ENT_COMPAT,'UTF-8',false);
        $this->doc .= '</title>'.DOKU_LF;
        $this->doc .= '</internalmedia>'.DOKU_LF;
    }

    function externalmedia ($src, $title=NULL, $align=NULL, $width=NULL,
                            $height=NULL, $cache=NULL, $linking=NULL) {
        $this->doc .= '<externalmedia align="' . $align . '" width="' . $width . '" height="' . $height . '" cache="' . $cache . '" linking="' . $linking . '">'.DOKU_LF;
        $this->doc .= '<src>'.DOKU_LF;
        $this->doc .= htmlspecialchars($src,ENT_COMPAT,'UTF-8',false);
        $this->doc .= '</src>'.DOKU_LF;
        $this->doc .= '<title>'.DOKU_LF;
        $this->doc .= htmlspecialchars($title,ENT_COMPAT,'UTF-8',false);
        $this->doc .= '</title>'.DOKU_LF;
        $this->doc .= '</externalmedia>'.DOKU_LF;
    }

    function internalmedialink (
        $src,$title=NULL,$align=NULL,$width=NULL,$height=NULL,$cache=NULL
        ) {
        $this->doc .= '<internalmedialink align="' . $align . '" width="' . $width . '" height="' . $height . '" cache="' . $cache . '">'.DOKU_LF;
        $this->doc .= '<src>'.DOKU_LF;
        $this->doc .= htmlspecialchars($src,ENT_COMPAT,'UTF-8',false);
        $this->doc .= '</src>'.DOKU_LF;
        $this->doc .= '<title>'.DOKU_LF;
        $this->doc .= htmlspecialchars($title,ENT_COMPAT,'UTF-8',false);
        $this->doc .= '</title>'.DOKU_LF;
        $this->doc .= '</internalmedialink>'.DOKU_LF;
    }

    function externalmedialink(
        $src,$title=NULL,$align=NULL,$width=NULL,$height=NULL,$cache=NULL
        ) {
        $this->doc .= '<externalmedialink align="' . $align . '" width="' . $width . '" height="' . $height . '" cache="' . $cache . '">'.DOKU_LF;
        $this->doc .= '<src>'.DOKU_LF;
        $this->doc .= htmlspecialchars($src,ENT_COMPAT,'UTF-8',false);
        $this->doc .= '</src>'.DOKU_LF;
        $this->doc .= '<title>'.DOKU_LF;
        $this->doc .= htmlspecialchars($title,ENT_COMPAT,'UTF-8',false);
        $this->doc .= '</title>'.DOKU_LF;
        $this->doc .= '</externalmedialink>'.DOKU_LF;
    }

    function table_open($maxcols = NULL, $numrows = NULL){
        $this->doc .= '<table maxcols="' . $maxcols . '" numrows="' . $numrows . '">'.DOKU_LF;
    }

    function table_close(){
        $this->doc .= '</table>'.DOKU_LF;
    }

    function tablerow_open(){
        $this->doc .= '<tablerow>'.DOKU_LF;
    }

    function tablerow_close(){
        $this->doc .= '</tablerow>'.DOKU_LF;
    }

    function tableheader_open($colspan = 1, $align = NULL){
        $this->doc .= '<tableheader colspan="' . $colspan . '" align="' . $align . '">'.DOKU_LF;
    }

    function tableheader_close(){
        $this->doc .= '</tableheader>'.DOKU_LF;
    }

    function tablecell_open($colspan = 1, $align = NULL){
        $this->doc .= '<tablecell colspan="' . $colspan . '" align="' . $align . '">'.DOKU_LF;
    }

    function tablecell_close(){
        $this->doc .= '</tablecell>'.DOKU_LF;
    }

}


//Setup VIM: ex: et ts=4 enc=utf-8 :
