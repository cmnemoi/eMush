#!/bin/bash

function install_perl_rename(){
    sudo apt install rename
    echo 'Rename command installed.'
}

function rename_translation_files(){
    cd crowdin_en
    rename "s/.fr.xlf$/.en.xlf/" *.xlf
    cd ../crowdin_es
    rename "s/.fr.xlf$/.es.xlf/" *.xlf
    cd ..
    echo 'Renamed translation files.'
}

function move_new_translation_files(){
    cp -r crowdin_fr/. fr
    cp -r crowdin_en/. en
    cp -r crowdin_es/. es
    echo 'Created new translation folders.'
}

function remove_crowdin_translation_folders(){
    rm -r crowdin_en crowdin_es crowdin_fr
    echo 'Removed crowdin translation folders.'
}

function main(){
   install_perl_rename
   rename_translation_files
   move_new_translation_files
   remove_crowdin_translation_folders
}

main
