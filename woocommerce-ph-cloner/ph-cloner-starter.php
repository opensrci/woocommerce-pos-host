<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */



/*
 * Entry point of cloner
 * 
 */
function ph_cloner_start(){
    
    // Load classes and functions.
    require_once PH_CLONER_PLUGIN_DIR . 'abstracts/class-ph-cloner-process.php';
    require_once PH_CLONER_PLUGIN_DIR . 'class-ph-cloner-request.php';
    require_once PH_CLONER_PLUGIN_DIR . 'class-ph-cloner-log.php';
    require_once PH_CLONER_PLUGIN_DIR . 'class-ph-cloner-process-manager.php';
    require_once PH_CLONER_PLUGIN_DIR . 'ph-utils.php';

    // Initialize request
    ph_cloner_request()->init();
    // Set the current user id, source id and target name, target title
    // so that the original user id can always be accessed by background processes.
    ph_cloner_request()->set( 'user_id', get_current_user_id() );
    ph_cloner_request()->set( 'source_id', '4' );
    ph_cloner_request()->set( 'target_name', 'test');
    ph_cloner_request()->set( 'target_title', 'test');
    ph_cloner_request()->set( 'clone_mode', 'core');
    ph_cloner_request()->set_up_vars();
    ph_cloner_request()->save();
    $req = ph_cloner_request()->get_request();

    //initilize log, if haven't 
    ph_cloner_log()->init();

    /* initialization process manager*/
    ph_cloner_process_manager()->do_clone();

}
/*
 * check status of cloner
 * 
 */
function ph_cloner_status(){
    //wait for ready.
    while ( ! ph_cloner_process_manager()->is_finished() 
            &&
            ! ph_cloner_log()->timeout()){
        //wait 
        sleep (5);
    };
        
    ph_cloner_process_manager()->finish();
    
}
    
