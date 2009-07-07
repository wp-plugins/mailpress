<?php

/*
 An attachment in Swift Mailer.
 
 This program is free software: you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation, either version 3 of the License, or
 (at your option) any later version.
 
 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with this program.  If not, see <http://www.gnu.org/licenses/>.
 
 */

//@require 'Swift/Mime/SimpleMimeEntity.php';
//@require 'Swift/Mime/ContentEncoder.php';
//@require 'Swift/Mime/HeaderSet.php';
//@require 'Swift/FileStream.php';
//@require 'Swift/KeyCache.php';

/**
 * An attachment, in a multipart message.
 * @package Swift
 * @subpackage Mime
 * @author Chris Corbyn
 */
class Swift_Mime_Attachment extends Swift_Mime_SimpleMimeEntity
{
  
  /** Recognized MIME types */
  private $_mimeTypes = array();
  
  /**
   * Create a new Attachment with $headers, $encoder and $cache.
   * @param Swift_Mime_HeaderSet $headers
   * @param Swift_Mime_ContentEncoder $encoder
   * @param Swift_KeyCache $cache
   * @param array $mimeTypes optional
   */
  public function __construct(Swift_Mime_HeaderSet $headers,
    Swift_Mime_ContentEncoder $encoder, Swift_KeyCache $cache,
    $mimeTypes = array())
  {
    parent::__construct($headers, $encoder, $cache);
    $this->setDisposition('attachment');
    $this->setContentType('application/octet-stream');
    $this->_mimeTypes = $mimeTypes;
  }
  
  /**
   * Get the nesting level used for this attachment.
   * Always returns {@link LEVEL_MIXED}.
   * @return int
   */
  public function getNestingLevel()
  {
    return self::LEVEL_MIXED;
  }
  
  /**
   * Get the Content-Disposition of this attachment.
   * By default attachments have a disposition of "attachment".
   * @return string
   */
  public function getDisposition()
  {
    return $this->_getHeaderFieldModel('Content-Disposition');
  }
  
  /**
   * Set the Content-Disposition of this attachment.
   * @param string $disposition
   */
  public function setDisposition($disposition)
  {
    if (!$this->_setHeaderFieldModel('Content-Disposition', $disposition))
    {
      $this->getHeaders()->addParameterizedHeader(
        'Content-Disposition', $disposition
        );
    }
    return $this;
  }
  
  /**
   * Get the filename of this attachment when downloaded.
   * @return string
   */
  public function getFilename()
  {
    return $this->_getHeaderParameter('Content-Disposition', 'filename');
  }
  
  /**
   * Set the filename of this attachment.
   * @param string $filename
   */
  public function setFilename($filename)
  {
    $this->_setHeaderParameter('Content-Disposition', 'filename', $filename);
    $this->_setHeaderParameter('Content-Type', 'name', $filename);
    return $this;
  }
  
  /**
   * Get the file size of this attachment.
   * @return int
   */
  public function getSize()
  {
    return $this->_getHeaderParameter('Content-Disposition', 'size');
  }
  
  /**
   * Set the file size of this attachment.
   * @param int $size
   */
  public function setSize($size)
  {
    $this->_setHeaderParameter('Content-Disposition', 'size', $size);
    return $this;
  }
  
  /**
   * Set the file that this attachment is for.
   * @param Swift_FileStream $file
   * @param string $contentType optional
   */
  public function setFile(Swift_FileStream $file, $contentType = null)
  {
    $this->setFilename(basename($file->getPath()));
    $this->setBody($file, $contentType);
    if (!isset($contentType))
    {
      $extension = strtolower(substr(
        $file->getPath(), strrpos($file->getPath(), '.') + 1
        ));
      
      if (array_key_exists($extension, $this->_mimeTypes))
      {
        $this->setContentType($this->_mimeTypes[$extension]);
      }
    }
    return $this;
  }
  
}
