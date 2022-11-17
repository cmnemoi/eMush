#!/bin/bash

function install_perl_rename(){
    sudo apt install rename
    echo 'Rename command installed.'
}

function delete_old_translation_folders(){
    rm -r en es fr
    echo 'Deleted old translation folders.'
}

function create_new_translation_folders(){
    cp -r crowdin_fr fr
    cp -r crowdin_en en
    cp -r crowdin_es es
    echo 'Created new translation folders.'
}

function rename_translation_files(){
    cd en
    rename "s/.fr.xlf$/.en.xlf/" *.xlf
    cd ../es
    rename "s/.fr.xlf$/.es.xlf/" *.xlf
    cd ..
    echo 'Renamed translation files.'
}

function remove_crowdin_translation_folders(){
    rm -r crowdin_en crowdin_es crowdin_fr
    echo 'Removed crowdin translation folders.'
}

function main(){
   install_perl_rename
   delete_old_translation_folders
   create_new_translation_folders
   rename_translation_files
   remove_crowdin_translation_folders
}

main
