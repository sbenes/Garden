<?php if (!defined('APPLICATION')) exit();
/*
Copyright 2008, 2009 Vanilla Forums Inc.
This file is part of Garden.
Garden is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
Garden is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
You should have received a copy of the GNU General Public License along with Garden.  If not, see <http://www.gnu.org/licenses/>.
Contact Vanilla Forums Inc. at support [at] vanillaforums [dot] com
*/

/**
 * Object for doing specific actions to a vbulletin import.
 */
class vBulletinImportModel extends Gdn_Model {
   /**
    * @var ImportModel
    */
   var $ImportModel = null;

   public function AfterImport() {
      // Set up the routes to redirect from their older counterparts.
      $Router = Gdn::Router();
      $Router->SetRoute('forumdisplay\.php\?f=(\d+)', 'vanilla/categories/$1', 'Permanent');
      $Router->SetRoute('showthread\.php\?t=(\d+)', 'vanilla/discussion/$1', 'Permanent');
      $Router->SetRoute('member\.php\?u=(\d+)', 'dashboard/profile/$1/x', 'Permanent');
      // Make different sizes of avatars
      $this->ProcessAvatars();
   }
   
   /**
    * Create different sizes of user photos.
    */
   public function ProcessAvatars() {
      $UploadImage = new Gdn_UploadImage();
      $UserData = $this->SQL->Select('u.*')->From('User u')->Where('u.Photo is not null')->Get();
      
      // Make sure the avatars folder exists.
      if (!file_exists(PATH_ROOT.'/uploads/userpics'))
         mkdir(PATH_ROOT.'/uploads/userpics');
      
      $ProfileHeight = C('Garden.Profile.MaxHeight', 1000);
      $ProfileWidth = C('Garden.Profile.MaxWidth', 250);
      $PreviewHeight = C('Garden.Preview.MaxHeight', 100);
      $PreviewWidth = C('Garden.Preview.MaxWidth', 75);
      $ThumbSize = C('Garden.Thumbnail.Size', 50);
      
      foreach ($UserData->Result() as $User) {
         try {
            $Image = PATH_ROOT . DS . 'uploads' . DS . $User->Photo;
            $ImageBaseName = pathinfo($Image, PATHINFO_BASENAME);            
            
            // Save profile size
            $UploadImage->SaveImageAs(
               $Image,
               PATH_ROOT.'/uploads/userpics/p'.$ImageBaseName,
               $ProfileHeight,
               $ProfileWidth
            );
            
            // Save preview size
            $UploadImage->SaveImageAs(
               $Image,
               PATH_ROOT.'/uploads/userpics/t'.$ImageBaseName,
               $PreviewHeight,
               $PreviewWidth
            );
            
            // Save thumbnail size
            $UploadImage->SaveImageAs(
               $Image,
               PATH_ROOT.'/uploads/userpics/n'.$ImageBaseName,
               $ThumbSize,
               $ThumbSize,
               TRUE
            );
         } catch (Exception $ex) { }
      }
   }
}
