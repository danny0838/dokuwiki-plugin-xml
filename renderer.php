<?php
/**
 * DokuWiki Plugin xml
 *
 * Original:
 * @author   Patrick Bueker <Patrick@patrickbueker.de>
 * @license  GPLv2 or later (http://www.gnu.org/licenses/gpl.html)
 *
 * Reworked:
 * @author   Danny Lin <danny0838@pchome.com.tw>
 *
 */

// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

if (!defined('DOKU_LF')) define('DOKU_LF', "\n");
if (!defined('DOKU_TAB')) define('DOKU_TAB', "\t");
require_once DOKU_INC . 'inc/parser/renderer.php';

class renderer_plugin_xml extends Doku_Renderer {
    
    function __construct() {
        $this->reset();
    }

    // allows renderer to be used again, clean out any per-use values
    function reset() {
        $this->info = array(
            'cache' => true, // may the rendered result cached?
            'toc'   => false, // render the TOC?
        );
        $this->precedinglevel = array();
        $this->nextHeader     = "";
        $this->helper         = &plugin_load('helper','xml');
        $this->doc            = '';
    }

    /**
     * Returns the format produced by this renderer.
     *
     * Has to be overidden by decendend classes
     */
    function getFormat(){return 'xml';}

    /**
     * handle plugin rendering
     */
    function plugin($name,$data){
        $plugin =& plugin_load('syntax',$name);
        if ($plugin == null) return;
        if ($this->helper->_xml_extension($this,$name,$data)) return;
        $plugin->render($this->getFormat(),$this,$data);
    }

    /**
     * Handles instructions
     *
     * @Stack:         add post-start linefeed and post-end linefeed
     * @Stack content: add post-start tab and post-end linefeed
     * @Block:         add post-end linefeed
     *
     * ~~SOMETHING>params~~  treated as <macro type="something" params />
     */
    function document_start() {
        global $ID;
        global $INFO;
        $this->doc  = '<?xml version="1.0" encoding="UTF-8"?>'.DOKU_LF;
        $this->doc .= '<document domain="' . DOKU_URL .'" id="' . cleanID($ID) . '" revision="' . $INFO['rev'] . '" lastmod="' . $INFO['lastmod'] . '">'.DOKU_LF;
        // store the content type headers in metadata
        $output_filename = str_replace(':','-',$ID).".xml";
        $headers = array(
            'Content-Type' => 'text/xml; charset=utf-8;',
            'Content-Disposition' => 'attachment; filename="'.$output_filename.'";',
        );
        p_set_metadata($ID,array('format' => array('xml' => $headers) ));
    }

    function document_end() {
        while(count($this->precedinglevel)>0)
        {
            $this->doc .= '</section>'.'<!--' . array_pop($this->precedinglevel) .  '-->'.DOKU_LF;
        }
        $this->doc .= '</document>'.DOKU_LF;
    }

    function header($text, $level, $pos) {
        if (!$text) return; //skip empty headlines
        $this->nextHeader  = '<header level="' . $level . '" pos="' . $pos . '">'.
        $this->nextHeader .= $this->_xmlEntities($text);
        $this->nextHeader .= '</header>'.DOKU_LF;
    }

    function section_open($level) {
        while(end($this->precedinglevel) >= $level)
        {
            $this->doc .= '</section>'.'<!--' . array_pop($this->precedinglevel) .  '-->'.DOKU_LF;
        }

        $this->doc .= '<section level="' . $level . '">'.DOKU_LF;
        $this->doc .= $this->nextHeader;
        $this->nextHeader = "";
        array_push($this->precedinglevel,$level);
    }

    function section_close() {
        #$this->doc .= '</section>'.DOKU_LF;
    }

    function nocache() {
        $this->info['cache'] = false;
        $this->doc .= '<macro name="nocache" />'.DOKU_LF;
    }

    function notoc() {
        $this->info['toc'] = false;
        $this->doc .= '<macro name="notoc" />'.DOKU_LF;
    }

    function cdata($text) {
        $this->doc .= $this->_xmlEntities($text);
    }

    function p_open() {
        $this->doc .= '<p>';
    }

    function p_close() {
        $this->doc .= '</p>'.DOKU_LF;
    }

    function linebreak() {
        $this->doc .= '<linebreak/>';
    }

    function hr() {
        $this->doc .= '<hr/>'.DOKU_LF;
    }

    function strong_open() {
        $this->doc .= '<strong>';
    }

    function strong_close() {
        $this->doc .= '</strong>';
    }

    function emphasis_open() {
        $this->doc .= '<emphasis>';
    }

    function emphasis_close() {
        $this->doc .= '</emphasis>';
    }

    function underline_open() {
        $this->doc .= '<underline>';
    }

    function underline_close() {
        $this->doc .= '</underline>';
    }

    function monospace_open() {
        $this->doc .= '<monospace>';
    }

    function monospace_close() {
        $this->doc .= '</monospace>';
    }

    function subscript_open() {
        $this->doc .= '<subscript>';
    }

    function subscript_close() {
        $this->doc .= '</subscript>';
    }

    function superscript_open() {
        $this->doc .= '<superscript>';
    }

    function superscript_close() {
        $this->doc .= '</superscript>';
    }

    function deleted_open() {
        $this->doc .= '<delete>';
    }

    function deleted_close() {
        $this->doc .= '</delete>';
    }

    function footnote_open() {
        $this->doc .= '<footnote>';
    }

    function footnote_close() {
        $this->doc .= '</footnote>';
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
        $this->doc .= DOKU_TAB.'<listitem level="' . $level . '">';
    }

    function listitem_close() {
        $this->doc .= '</listitem>'.DOKU_LF;
    }

    function listcontent_open() {
        $this->doc .= '<listcontent>';
    }

    function listcontent_close() {
        $this->doc .= '</listcontent>';
    }

    function unformatted($text) {
        $this->doc .= '<unformatted>';
        $this->doc .= $this->_xmlEntities($text);
        $this->doc .= '</unformatted>';
    }

    function php($text) {
        $this->doc .= '<php>';
        $this->doc .= $this->_xmlEntities($text);
        $this->doc .= '</php>';
    }

    function phpblock($text) {
        $this->doc .= '<phpblock>';
        $this->doc .= $this->_xmlEntities($text);
        $this->doc .= '</phpblock>'.DOKU_LF;
    }

    function html($text) {
        $this->doc .= '<html>';
        $this->doc .= $this->_xmlEntities($text);
        $this->doc .= '</html>';
    }

    function htmlblock($text) {
        $this->doc .= '<htmlblock>';
        $this->doc .= $this->_xmlEntities($text);
        $this->doc .= '</htmlblock>'.DOKU_LF;
    }

    function preformatted($text) {
        $this->doc .= '<preformatted>';
        $this->doc .= $this->_xmlEntities($text);
        $this->doc .= '</preformatted>'.DOKU_LF;
    }

    function quote_open() {
        $this->doc .= '<quote>';
    }

    function quote_close() {
        $this->doc .= '</quote>'.DOKU_LF;
    }

    function code($text, $lang = NULL, $file = NULL) {
        $this->doc .= '<code lang="' . $lang . '" file="' . $file . '">';
        $this->doc .= $this->_xmlEntities($text);
        $this->doc .= '</code>'.DOKU_LF;
    }

    function file($text, $lang = NULL, $file = NULL) {
        $this->doc .= '<file lang="' . $lang . '" file="' . $file . '">';
        $this->doc .= $this->_xmlEntities($text);
        $this->doc .= '</file>'.DOKU_LF;
    }

    function acronym($acronym) {
        $this->doc .= '<acronym>';
        $this->doc .= $this->_xmlEntities($acronym);
        $this->doc .= '</acronym>';
    }

    function smiley($smiley) {
        $this->doc .= '<smiley>';
        $this->doc .= $this->_xmlEntities($smiley);
        $this->doc .= '</smiley>';
    }

    function entity($entity) {
        $this->doc .= '<entity>';
        $this->doc .= $this->_xmlEntities($entity);
        $this->doc .= '</entity>';
    }

    // 640x480 ($x=640, $y=480)
    function multiplyentity($x, $y) {
        $this->doc .= '<multiplyentity>';
        $this->doc .= '<x>'.$this->_xmlEntities($x).'</x>';
        $this->doc .= '<y>'.$this->_xmlEntities($y).'</y>';
        $this->doc .= '</multiplyentity>';
    }

    function singlequoteopening() {
        $this->doc .= '<singlequote>';
    }

    function singlequoteclosing() {
        $this->doc .= '</singlequote>';
    }

    function apostrophe() {
        $this->doc .= '<apostrophe/>';
    }

    function doublequoteopening() {
        $this->doc .= '<doublequote>';
    }

    function doublequoteclosing() {
        $this->doc .= '</doublequote>';
    }

    // $link like 'SomePage'
    function camelcaselink($link) {
        $this->doc .= '<link type="camelcase" href="' . $this->_xmlEntities($link) . '">';
        $this->doc .= $this->_xmlEntities($link);
        $this->doc .= '</link>';
    }

    function locallink($hash, $name = NULL) {
        $this->doc .= '<link type="locallink" href="' . $this->_xmlEntities($hash) . '">';
        $this->doc .= $this->_getLinkTitle($name, $hash);
        $this->doc .= '</link>';
    }

    // $link like 'wiki:syntax', $title could be an array (media)
    function internallink($link, $title = NULL) {
        $this->doc .= '<link type="internal" href="' . $this->_xmlEntities($link) . '">';
        $this->doc .= $this->_getLinkTitle($title, $link);
        $this->doc .= '</link>';
    }

    // $link is full URL with scheme, $title could be an array (media)
    function externallink($link, $title = NULL) {
        $this->doc .= '<link type="external" href="' . $this->_xmlEntities($link) . '">';
        $this->doc .= $this->_getLinkTitle($title, $link);
        $this->doc .= '</link>';
    }

    // $link is the original link - probably not much use
    // $wikiName is an indentifier for the wiki
    // $wikiUri is the URL fragment to append to some known URL
    function interwikilink($link, $title = NULL, $wikiname, $wikiuri) {
        $this->doc .= '<link type="interwiki" href="' . $this->_xmlEntities($link) . '" wikiname="' . $this->_xmlEntities($wikiname) . '" wikiuri="' . $this->_xmlEntities($wikiuri) . '">';
        $this->doc .= $this->_getLinkTitle($title, $link);
        $this->doc .= '</link>';
    }

    // Link to file on users OS, $title could be an array (media)
    function filelink($link, $title = NULL) {
        $this->doc .= '<link type="filelink" href="' . $this->_xmlEntities($link) . '">';
        $this->doc .= $this->_getLinkTitle($title, $link);
        $this->doc .= '</link>';
    }

    // Link to a Windows share, , $title could be an array (media)
    function windowssharelink($link, $title = NULL) {
        $this->doc .= '<link type="windowssharelink" href="' . $this->_xmlEntities($link) . '">';
        $this->doc .= $this->_getLinkTitle($title, $link);
        $this->doc .= '</link>';
    }

//  function email($address, $title = NULL) {}
    function emaillink($address, $name = NULL) {
        $this->doc .= '<link type="emaillink" href="' . $this->_xmlEntities($address) . '">';
        $this->doc .= $this->_getLinkTitle($name, $address);
        $this->doc .= '</link>';
}

    function internalmedia ($src, $title=NULL, $align=NULL, $width=NULL, $height=NULL, $cache=NULL, $linking=NULL) {
        $this->doc .= '<media type="internalmedia" src="' . $this->_xmlEntities($src) . '" align="' . $align . '" width="' . $width . '" height="' . $height . '" cache="' . $cache . '" linking="' . $linking . '">'.DOKU_LF;
        $this->doc .= $this->_xmlEntities($title, $src);
        $this->doc .= '</media>';
    }

    function externalmedia ($src, $title=NULL, $align=NULL, $width=NULL, $height=NULL, $cache=NULL, $linking=NULL) {
        $this->doc .= '<media type="externalmedia" src="' . $this->_xmlEntities($src) . '" align="' . $align . '" width="' . $width . '" height="' . $height . '" cache="' . $cache . '" linking="' . $linking . '">';
        $this->doc .= $this->_xmlEntities($title, $src);
        $this->doc .= '</media>';
    }

    function internalmedialink ($src, $title=NULL, $align=NULL, $width=NULL, $height=NULL, $cache=NULL) {
        $this->doc .= '<link type="internalmedialink" href="' . $this->_xmlEntities($src) . '" align="' . $align . '" width="' . $width . '" height="' . $height . '" cache="' . $cache . '">';
        $this->doc .= $this->_xmlEntities($title, $src);
        $this->doc .= '</link>';
    }

    function externalmedialink($src, $title=NULL, $align=NULL, $width=NULL, $height=NULL, $cache=NULL) {
        $this->doc .= '<link type="externalmedialink"  href="' . $this->_xmlEntities($src) . '" align="' . $align . '" width="' . $width . '" height="' . $height . '" cache="' . $cache . '">';
        $this->doc .= $this->_xmlEntities($title, $src);
        $this->doc .= '</externalmedialink>';
    }

    function table_open($maxcols = NULL, $numrows = NULL){
        $this->doc .= '<table maxcols="' . $maxcols . '" numrows="' . $numrows . '">'.DOKU_LF;
    }

    function table_close(){
        $this->doc .= '</table>'.DOKU_LF;
    }

    function tablerow_open(){
        $this->doc .= DOKU_TAB.'<tablerow>';
    }

    function tablerow_close(){
        $this->doc .= '</tablerow>'.DOKU_LF;
    }

    function tableheader_open($colspan = 1, $align = NULL){
        $this->doc .= '<tableheader colspan="' . $colspan . '" align="' . $align . '">';
    }

    function tableheader_close(){
        $this->doc .= '</tableheader>';
    }

    function tablecell_open($colspan = 1, $align = NULL){
        $this->doc .= '<tablecell colspan="' . $colspan . '" align="' . $align . '">';
    }

    function tablecell_close(){
        $this->doc .= '</tablecell>';
    }

    /**
     * Private functions for internal handling
     */
    function _xmlEntities($text){
        return htmlspecialchars($text,ENT_COMPAT,'UTF-8');
    }
    
    function _getLinkTitle($title, $default){
        if ( is_array($title) ) return $this->_imageTitle($title);
        if ( is_null($title) || trim($title)=='' ) $title = $default;
        return $this->_xmlEntities($title);
    }
    
    function _imageTitle($img) {
        extract($img);
        $out .= '<media type="' . $type . '" src="' . $this->_xmlEntities($src) . '" align="' . $align . '" width="' . $width . '" height="' . $height . '" cache="' . $cache . '" linking="' . $linking . '">';
        $out .= $this->_xmlEntities($title);
        $out .= '</media>';
        return $out;
    }
}
