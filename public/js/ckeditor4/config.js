/**
 * @license Copyright (c) 2003-2015, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.md or http://ckeditor.com/license
 */

CKEDITOR.editorConfig = function( config ) {
	// Define changes to default configuration here. For example:
	// config.language = 'fr';
	// config.uiColor = '#AADC6E';
        

config.pasteFromWordPromptCleanup = false;
config.pasteFromWordRemoveFontStyles = false;
config.pasteFromWordRemoveStyles = false;
config.resize_dir = 'both';

CKEDITOR.config.forcePasteAsPlainText = false; 
CKEDITOR.config.basicEntities = true;

CKEDITOR.config.allowedContent = true; // don't filter my data
};
