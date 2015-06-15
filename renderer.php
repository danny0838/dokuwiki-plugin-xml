<?php
/**
 * DokuWiki Plugin xml
 *
 * @author   Patrick Bueker <Patrick@patrickbueker.de>
 * @author   Danny Lin <danny0838@gmail.com>
 * @license  GPLv2 or later (http://www.gnu.org/licenses/gpl.html)
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

    /**
     * Allows renderer to be used again. Clean out any per-use values.
     */
    function reset() {
        $this->info = array(
            'cache' => true, // may the rendered result cached?
            'toc'   => false, // render the TOC?
        );
        $this->precedinglevel = array();
        $this->nextHeader     = "";
        $this->helper         = &plugin_load('helper','xml');
        $this->doc            = '';
        $this->tagStack       = array();
    }

    /**
     * Returns the format produced by this renderer.
     *
     * @return string
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
        while(count($this->precedinglevel)>0) {
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
        $this->_openTag($this, 'p_close', array());
    }

    function p_close() {
        $this->_closeTags($this, __FUNCTION__);
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
        $this->_openTag($this, 'strong_close', array());
    }

    function strong_close() {
        $this->_closeTags($this, __FUNCTION__);
        $this->doc .= '</strong>';
    }

    function emphasis_open() {
        $this->doc .= '<emphasis>';
        $this->_openTag($this, 'emphasis_close', array());
    }

    function emphasis_close() {
        $this->_closeTags($this, __FUNCTION__);
        $this->doc .= '</emphasis>';
    }

    function underline_open() {
        $this->doc .= '<underline>';
        $this->_openTag($this, 'underline_close', array());
    }

    function underline_close() {
        $this->_closeTags($this, __FUNCTION__);
        $this->doc .= '</underline>';
    }

    function monospace_open() {
        $this->doc .= '<monospace>';
        $this->_openTag($this, 'monospace_close', array());
    }

    function monospace_close() {
        $this->_closeTags($this, __FUNCTION__);
        $this->doc .= '</monospace>';
    }

    function subscript_open() {
        $this->doc .= '<subscript>';
        $this->_openTag($this, 'subscript_close', array());
    }

    function subscript_close() {
        $this->_closeTags($this, __FUNCTION__);
        $this->doc .= '</subscript>';
    }

    function superscript_open() {
        $this->doc .= '<superscript>';
        $this->_openTag($this, 'superscript_close', array());
    }

    function superscript_close() {
        $this->_closeTags($this, __FUNCTION__);
        $this->doc .= '</superscript>';
    }

    function deleted_open() {
        $this->doc .= '<delete>';
        $this->_openTag($this, 'deleted_close', array());
    }

    function deleted_close() {
        $this->_closeTags($this, __FUNCTION__);
        $this->doc .= '</delete>';
    }

    function footnote_open() {
        $this->doc .= '<footnote>';
        $this->_openTag($this, 'footnote_close', array());
    }

    function footnote_close() {
        $this->_closeTags($this, __FUNCTION__);
        $this->doc .= '</footnote>';
    }

    function listu_open() {
        $this->doc .= '<listu>'.DOKU_LF;
        $this->_openTag($this, 'listu_close', array());
    }

    function listu_close() {
        $this->_closeTags($this, __FUNCTION__);
        $this->doc .= '</listu>'.DOKU_LF;
    }

    function listo_open() {
        $this->doc .= '<listo>'.DOKU_LF;
        $this->_openTag($this, 'listo_close', array());
    }

    function listo_close() {
        $this->_closeTags($this, __FUNCTION__);
        $this->doc .= '</listo>'.DOKU_LF;
    }

    function listitem_open($level) {
        $this->doc .= DOKU_TAB.'<listitem level="' . $level . '">';
        $this->_openTag($this, 'listitem_close', array());
    }

    function listitem_close() {
        $this->_closeTags($this, __FUNCTION__);
        $this->doc .= '</listitem>'.DOKU_LF;
    }

    function listcontent_open() {
        $this->doc .= '<listcontent>';
        $this->_openTag($this, 'listcontent_close', array());
    }

    function listcontent_close() {
        $this->_closeTags($this, __FUNCTION__);
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
        $this->_openTag($this, 'quote_close', array());
    }

    function quote_close() {
        $this->_closeTags($this, __FUNCTION__);
        $this->doc .= '</quote>'.DOKU_LF;
    }

    function code($text, $lang = null, $file = null) {
        $this->doc .= '<code lang="' . $lang . '" file="' . $file . '">';
        $this->doc .= $this->_xmlEntities($text);
        $this->doc .= '</code>'.DOKU_LF;
    }

    function file($text, $lang = null, $file = null) {
        $this->doc .= '<file lang="' . $lang . '" file="' . $file . '">';
        $this->doc .= $this->_xmlEntities($text);
        $this->doc .= '</file>'.DOKU_LF;
    }

    function acronym($acronym) {
        $this->doc .= '<acronym data="' . $this->_xmlEntities($this->acronyms[$acronym]) . '">';
        $this->doc .= $this->_xmlEntities($acronym);
        $this->doc .= '</acronym>';
    }

    function smiley($smiley) {
        $this->doc .= '<smiley>';
        $this->doc .= $this->_xmlEntities($smiley);
        $this->doc .= '</smiley>';
    }

    function entity($entity) {
        $this->doc .= '<entity data="' . $this->_xmlEntities($this->entities[$entity]) . '">';
        $this->doc .= $this->_xmlEntities($entity);
        $this->doc .= '</entity>';
    }

    /**
     * Multiply entities are of the form: 640x480 where $x=640 and $y=480
     *
     * @param string $x The left hand operand
     * @param string $y The rigth hand operand
     */
    function multiplyentity($x, $y) {
        $this->doc .= '<multiplyentity>';
        $this->doc .= '<x>'.$this->_xmlEntities($x).'</x>';
        $this->doc .= '<y>'.$this->_xmlEntities($y).'</y>';
        $this->doc .= '</multiplyentity>';
    }

    function singlequoteopening() {
        global $lang;
        $this->doc .= '<singlequote open="' . $this->_xmlEntities($lang['singlequoteopening']) . '" close="' . $this->_xmlEntities($lang['singlequoteclosing']) . '">';
        $this->_openTag($this, 'singlequoteclosing', array());
    }

    function singlequoteclosing() {
        $this->_closeTags($this, __FUNCTION__);
        $this->doc .= '</singlequote>';
    }

    function apostrophe() {
        global $lang;
        $this->doc .= '<apostrophe data="' . $this->_xmlEntities($lang['apostrophe']) . '"/>';
    }

    function doublequoteopening() {
        global $lang;
        $this->doc .= '<doublequote open="' . $this->_xmlEntities($lang['doublequoteopening']) . '" close="' . $this->_xmlEntities($lang['doublequoteclosing']) . '">';
        $this->_openTag($this, 'doublequoteclosing', array());
    }

    function doublequoteclosing() {
        $this->_closeTags($this, __FUNCTION__);
        $this->doc .= '</doublequote>';
    }

    /**
     * Links in CamelCase format.
     *
     * @param string $link Link text
     */
    function camelcaselink($link) {
        $this->doc .= '<link type="camelcase" href="' . $this->_xmlEntities($link) . '">';
        $this->doc .= $this->_xmlEntities($link);
        $this->doc .= '</link>';
    }

    function locallink($hash, $name = null) {
        $this->doc .= '<link type="locallink" href="' . $this->_xmlEntities($hash) . '">';
        $this->doc .= $this->_getLinkTitle($name, $hash);
        $this->doc .= '</link>';
    }

    /**
     * Links of the form 'wiki:syntax', where $title is either a string or (for
     * media links) an array.
     *
     * @param string $link The link text
     * @param mixed $title Title text (array for media links)
     */
    function internallink($link, $title = null) {
        $this->doc .= '<link type="internal" href="' . $this->_xmlEntities($link) . '">';
        $this->doc .= $this->_getLinkTitle($title, $this->_simpleTitle($link));
        $this->doc .= '</link>';
    }

    /**
     * Full URL links with scheme. $title could be an array, for media links.
     *
     * @param string $link The link text
     * @param mixed $title Title text (array for media links)
     */
    function externallink($link, $title = null) {
        $this->doc .= '<link type="external" href="' . $this->_xmlEntities($link) . '">';
        $this->doc .= $this->_getLinkTitle($title, $link);
        $this->doc .= '</link>';
    }

    /**
     * @param string $link the original link - probably not much use
     * @param string $title
     * @param string $wikiname an indentifier for the wiki
     * @param string $wikiuri the URL fragment to append to some known URL
     */
    function interwikilink($link, $title, $wikiname, $wikiuri) {
        $this->doc .= '<link type="interwiki" href="' . $this->_resolveInterWiki($wikiname, $wikiuri) . '" wikiname="' . $this->_xmlEntities($wikiname) . '" wikiuri="' . $this->_xmlEntities($wikiuri) . '">';
        $this->doc .= $this->_getLinkTitle($title, $link);
        $this->doc .= '</link>';
    }

    /**
     * Link to a file on user's OS. $title could be an array (for media links).
     *
     * @param string $link
     * @param mixed $title 
     */
    function filelink($link, $title = null) {
        $this->doc .= '<link type="filelink" href="' . $this->_xmlEntities($link) . '">';
        $this->doc .= $this->_getLinkTitle($title, $link);
        $this->doc .= '</link>';
    }

    /**
     * Link to a Windows share, $title could be an array (media)
     *
     * @param string $link
     * @param mixed $title
     */
    function windowssharelink($link, $title = null) {
        $this->doc .= '<link type="windowssharelink" href="' . $this->_xmlEntities($link) . '">';
        $this->doc .= $this->_getLinkTitle($title, $link);
        $this->doc .= '</link>';
    }

    function emaillink($address, $name = null) {
        $this->doc .= '<link type="emaillink" href="' . $this->_xmlEntities($address) . '">';
        $this->doc .= $this->_getLinkTitle($name, $address);
        $this->doc .= '</link>';
    }

    /**
     * Render media that is internal to the wiki.
     *
     * @param string $src
     * @param string $title
     * @param string $align
     * @param string $width
     * @param string $height
     * @param string $cache
     * @param string $linking
     */
    function internalmedia ($src, $title=null, $align=null, $width=null, $height=null, $cache=null, $linking=null) {
        $this->media('internalmedia', $src, $title, $align, $width, $height, $cache, $linking);
    }

    /**
     * Render media that is external to the wiki.
     *
     * @param string $src
     * @param string $title
     * @param string $align
     * @param string $width
     * @param string $height
     * @param string $cache
     * @param string $linking
     */
    function externalmedia ($src, $title=null, $align=null, $width=null, $height=null, $cache=null, $linking=null) {
        $this->media('externalmedia', $src, $title, $align, $width, $height, $cache, $linking);
    }

    /**
     * Render media elements.
     * @see Doku_Renderer_xhtml::internalmedia()
     *
     * @param string $type Either 'internalmedia' or 'externalmedia'
     * @param string $src
     * @param string $title
     * @param string $align
     * @param string $width
     * @param string $height
     * @param string $cache
     * @param string $linking
     */
    function media($type, $src, $title=null, $align=null, $width=null, $height=null, $cache=null, $linking=null) {
        global $ID;
        list($ext, $mime, $dl) = mimetype($src, false);

        // Everything is linked directly apart from images without 'linkonly'.
        $details = (substr($mime, 0, 5)=='image' && $linking!='linkonly');
        $link_attrs = array('id'=>$ID, 'cache'=>$cache);
        $href = ml($src, $link_attrs, !$details, null, true);

        // Filename
        $basename = ($type=='externalmedia') ? basename($src) : noNS($src);

        $this->doc .= '<media type="' . $type . '" src="' . $this->_xmlEntities($src) . '"';
        $this->doc .= ' basename="' . $basename . '" href="' . $href . '"';
        $this->doc .= ' mimetype="' . $mime . '" ext="' . $ext . '"';
        $this->doc .= ' align="' . $align . '" width="' . $width . '" height="' . $height . '"';
        $this->doc .= ' cache="' . $cache . '" linking="' . $linking . '">'.DOKU_LF;
        $this->doc .= $this->_xmlEntities($title, $src);
        $this->doc .= '</media>';
    }

    function internalmedialink ($src, $title=null, $align=null, $width=null, $height=null, $cache=null) {
        $this->doc .= '<link type="internalmedialink" href="' . $this->_xmlEntities($src) . '" align="' . $align . '" width="' . $width . '" height="' . $height . '" cache="' . $cache . '">';
        $this->doc .= $this->_xmlEntities($title, $src);
        $this->doc .= '</link>';
    }

    function externalmedialink($src, $title=null, $align=null, $width=null, $height=null, $cache=null) {
        $this->doc .= '<link type="externalmedialink"  href="' . $this->_xmlEntities($src) . '" align="' . $align . '" width="' . $width . '" height="' . $height . '" cache="' . $cache . '">';
        $this->doc .= $this->_xmlEntities($title, $src);
        $this->doc .= '</externalmedialink>';
    }

    function table_open($maxcols = null, $numrows = null){
        $this->doc .= '<table maxcols="' . $maxcols . '" numrows="' . $numrows . '">'.DOKU_LF;
        $this->_openTag($this, 'table_close', array());
    }

    function table_close(){
        $this->_closeTags($this, __FUNCTION__);
        $this->doc .= '</table>'.DOKU_LF;
    }

    function tablerow_open(){
        $this->doc .= DOKU_TAB.'<tablerow>';
        $this->_openTag($this, 'tablerow_close', array());
    }

    function tablerow_close(){
        $this->_closeTags($this, __FUNCTION__);
        $this->doc .= '</tablerow>'.DOKU_LF;
    }

    function tableheader_open($colspan = 1, $align = null, $rowspan = 1){
        $this->doc .= '<tableheader';
        if ($colspan>1) $this->doc .= ' colspan="' . $colspan . '"';
        if ($rowspan>1) $this->doc .= ' rowspan="' . $rowspan . '"';
        if ($align) $this->doc .= ' align="' . $align . '"';
        $this->doc .= '>';
        $this->_openTag($this, 'tableheader_close', array());
    }

    function tableheader_close(){
        $this->_closeTags($this, __FUNCTION__);
        $this->doc .= '</tableheader>';
    }

    function tablecell_open($colspan = 1, $align = null, $rowspan = 1) {
        $this->doc .= '<tablecell';
        if ($colspan>1) $this->doc .= ' colspan="' . $colspan . '"';
        if ($rowspan>1) $this->doc .= ' rowspan="' . $rowspan . '"';
        if ($align) $this->doc .= ' align="' . $align . '"';
        $this->doc .= '>';
        $this->_openTag($this, 'tablecell_close', array());
    }

    function tablecell_close(){
        $this->_closeTags($this, __FUNCTION__);
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
    
    function _openTag($class, $func, $data=null) {
        $this->tagStack[] = array($class, $func, $data);
    }

    function _closeTags($class=null, $func=null) {
        if ($this->tagClosing==true) return;  // skip nested calls
        $this->tagClosing = true;
        while(count($this->tagStack)>0) {
            list($lastclass, $lastfunc, $lastdata) = array_pop($this->tagStack);
            if (!($lastclass===$class && $lastfunc==$func)) call_user_func_array( array($lastclass, $lastfunc), $lastdata );
            else break;
        }
        $this->tagClosing = false;
    }
}
