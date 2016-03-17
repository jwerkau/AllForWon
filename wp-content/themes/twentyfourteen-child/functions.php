<?php
//Global variables.
global $login_error;
global $register_error;
global $analysisPage;
global $offensiveStatisticsPage;
global $pitchingStatisticsPage;
global $exploringStatisticsPage;
global $goodReadsPage;
global $homePage;
global $loginPage;
global $logoutPage;
global $profilePage;
global $registerPage;
global $registrationSuccesfullPage;

//ACTIONS
add_action( 'get_header', 'redirect_to_sign_in_page' );

//FILTERS
add_filter( 'show_admin_bar', '__return_false');
add_filter( 'wp_nav_menu_items', 'add_register_and_profile_item_to_menu', 50, 2 );
add_filter( 'wp_nav_menu_items', 'add_login_out_item_to_menu', 50, 2 );
add_filter( 'login_redirect', 'soi_login_redirect', 10, 3 );

add_theme_support( 'post-thumbnails' );

//Short codes
add_shortcode( 'ABtotal', 'display_AB_total');
add_shortcode( 'RegistrationForm', 'display_registration_form');
add_shortcode( 'LoginForm', 'display_login_form');
add_shortcode( 'TemplateMap', 'display_template_map');
add_shortcode( 'HittingMap', 'display_hitting_map');
add_shortcode( 'PitchingMap', 'display_pitching_map');
add_shortcode( 'ResultsOptions', 'display_results_options');
add_shortcode( 'HittingStatistics', 'display_hitting_statistics');
add_shortcode( 'HotColdLegend', 'display_hot_cold_legend');
add_shortcode( 'TeamPreferences', 'display_team_preferences');
add_shortcode( 'UserProfile', 'display_user_profile');
add_shortcode( 'ExportOptions', 'display_export_options');
add_shortcode( 'DisplayOffensiveEditTable', 'display_offensive_edit_table');

//FUNCTIONS
//Add Register/Profile tab to primary menu
function add_register_and_profile_item_to_menu( $items, $args ){
    global $current_user;
    global $profilePage;
    global $registerPage;
    //change theme location with your them location name
    if( is_admin() ||  $args->theme_location != 'primary' ) {
	return $items; 
    }
    if( is_user_logged_in( ) ) {
        get_currentuserinfo();
	$link = '<a href='.$profilePage.' title="' .  __( 'Welcome, ' . $current_user->first_name ) .'">' . __( 'Welcome, ' . $current_user->first_name ) . '</a>';
    }
    else{
	$link = '<a href='.$registerPage.' title="' .  __( 'Register' ) .'">' . __( 'Register' ) . '</a>';
    }
    return $items.= '<li id="register-and-profile-link" class="menu-item menu-type-link">'. $link . '</li>';
}
//Add Login/Logout tab to primary menu
function add_login_out_item_to_menu( $items, $args ){
    global $logoutPage;
    global $loginPage;
    
    //change theme location with your them location name
    if( is_admin() ||  $args->theme_location != 'primary' ) {
        return $items; 
    }
    if( is_user_logged_in( ) ) {
	$link = '<a href="'.wp_logout_url($logoutPage).'" title="' .  __( 'Logout' ) .'">' . __( 'Logout' ) . '</a>';
    }
    else  {
        $link = '<a href='.$loginPage.' title="' .  __( 'Login' ) .'">' . __( 'Login' ) . '</a>';
    }
    return $items.= '<li id="log-in-out-link" class="menu-item menu-type-link">'. $link . '</li>';
}
//Redirect to home after login.
function soi_login_redirect( $redirect_to,$request, $user  ) {
    return ( is_array( $user->roles ) && in_array( 'administrator', $user->roles ) ) ? admin_url() : site_url();
} // end soi_login_redirect
//Only let logged in users see pitching/offensive statistics page.
function redirect_to_sign_in_page() {
    global $loginPage;
    if (!is_user_logged_in() && (is_page(355)  || is_page(347))) {
          wp_redirect($loginPage);
		  exit;
    }
}
//Display registration form shortcode.
function display_registration_form(){  
    global $register_error;
    $registration_form = 
    '<div id="my_register_form">
        <form action="" method="post">
            <label for="regiser_error" class="error_messages">' .$register_error. '</label><br>
            <label for="firstname">First Name: </label>
            <input class="text" id="firstname" type="text" name="firstname" value="" required/><br><br>
            <label for="lastname">Last Name: </label>
            <input class="text" id="lastname" type="text" name="lastname" value="" required/><br><br>
            <label for="username">Username: </label>
            <input class="text" id="username" type="text" name="username" value="" required/><br><br>
            <label for="email">Email address: </label>
            <input class="text" id="email" type="text" name="email" value="" /><br><br>
            <label for="password">Password: </label>
            <input class="text" id="password" type="password" name="password" value="" required/><br><br>
            <label for="retypepassword">Retype Password: </label>
            <input class="text" id="retypepassword" type="password" name="retypepassword" value="" required/><br><br>
            <input class="submitbutton" type="submit" name="registersubmit" value="SignUp" />
        </form>
    </div>'; 
    return $registration_form;
}
//Register user
function register_user() {
    global $wpdb;
    global $registrationSuccesfullPage;
    $firstname = $_POST['firstname'];
    $lastname = $_POST['lastname'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $retypepassword = $_POST['retypepassword'];
    $email = $_POST['email'];
    
    //Grab any matching usernames
    $usernamequery = $wpdb->query("SELECT user_login FROM `wp_users` WHERE user_login = '$username'");
   
    if ($usernamequery > 0){
        $register_error = "Error: The username is already taken.";
    }
    else if ($password != $retypepassword){
        $register_error = "Error: The passwords do not match.  Please try retyping the passwords so that they match.";
    }
    else {
        //Create user
        $userdata = array(
            'user_login'  =>  $username,
            'user_pass'    =>  $password,
            'user_email'   =>  $email,  
            'first_name'  =>   $firstname,
            'last_name'   =>   $lastname
        );

        $user_id = wp_insert_user( $userdata ) ;

    //On success
        if( is_wp_error($user_id) ) {
        echo "User Not Created.";
        } 

        //Login user
        wp_clear_auth_cookie();
        $creds = array();
        $creds['user_login'] = $username;
        $creds['user_password'] = $password;
        $creds['remember'] = true;
        wp_signon( $creds, false );

        //MAY NEED TO CHECK FOR LOGIN ERRORS HERE

        //Redirect to succesful login
        wp_redirect($registrationSuccesfullPage);
        exit;

        //Send email welcoming user.
        /*$from = get_option('admin_email');  
        $headers = 'From: '.$from . "\r\n";  
        $subject = "Registration successful";  
        $msg = "Registration successful.\nYour login details\nUsername: $username\nPassword: $random_password";  
        echo "$from";
        wp_mail( $email, $subject, $msg, $headers );*/  
    }
    return $register_error;
}
//Display login form shortcode.
function display_login_form () {
    global $login_error;
    $loginform = 
    '<div id="my_login_form">
        <form action="" method="post">
            <label for="login_error" class="error_messages">' . $login_error . ' </label><br>
            <label for="username">Username: </label>	 	 
            <input class="text" id="username" type="text" name="username" value="" required/><br><br>	 	 
            <label for="password">Password: </label>	 	 
            <input class="text" id="password" type="password" name="password" value="" required/><br><br>	 	 
            <input id="loginbtn" class="submitbutton" type="submit" name="login" value="Login" />
       </form>
    </div>';
    return $loginform;
}
function login_user() {
    // Define $username and $password 
    $username=$_POST['username']; 
    $password=($_POST['password']);

    //Check username and password for characters
    $username = stripslashes($username);
    $password = stripslashes($password);
    $username = mysql_real_escape_string($username);
    $password = mysql_real_escape_string($password);

    //Login valid user
    wp_clear_auth_cookie();
    $creds = array();
    $creds['user_login'] = $username;
    $creds['user_password'] = $password;
    $creds['remember'] = true;
    $user = wp_signon( $creds, false );

    if (is_wp_error($user)) {
        $login_error = $user->get_error_message();
    }
    else {
        //Set current user
        wp_set_current_user($user->ID);
        $current_user = wp_get_current_user();
        if (is_wp_error($current_user)) {
            $login_error = $user->get_error_message();
        }
    }
    //If succesful login send user to home page.
    if (is_user_logged_in() ) {
        wp_redirect(home_url());
        exit();
    } 
    return $login_error;
}
//Display profile shortcode
function display_user_profile(){
    $current_user = wp_get_current_user();
    
    $FirstName = get_user_meta($current_user->ID, 'first_name', true);
    $LastName = get_user_meta($current_user->ID, 'last_name', true);
    $UserName = $current_user->user_login;
    $UserProfile = 
    '<div id="user_profile_form">
        <form action="" method="post">
            <label class="ProfileLabel" for="ProfileFirstName">First Name: </label>
            <input class="text" id="ProfileFirstName" type="text" name="ProfileFirstName" value="'.$FirstName.'"/><br>
            <label class="ProfileLabel" for="ProfileLastName">Last Name: </label>
            <input class="text" id="ProfileLastName" type="text" name="ProfileLastName" value="'.$LastName.'"/><br>
            <label class="ProfileLabel" for="ProfileUsername">Last Name: </label>
            <input class="text" id="ProfileUserName" type="text" name="ProfileUsertName" value="'.$UserName.'"/><br><br>
            <label class="ProfileLabel" for="ProfileNewPassword">New Password: </label>
            <input class="text" id="ProfileNewPassword" type="password" name="ProfileNewPassword" value=""/><br>
            <label class="ProfileLabel" for="ProfileRetypePassword">Retype Password: </label>
            <input class="text" id="ProfileRetypePassword" type="password" name="ProfileRetypePassword" value=""/><br><br>    
        </form>
    </div>'; 
 
    return $UserProfile;
}
//Display team preferences
function display_team_preferences (){
    $TeamPreferences = 
    '<div id="team_preferences_form">
        <form action="" method="post">
            <label class="Teams" for="TeamPreferences">Favorite Teams: </label><br>
            <label class="Teams" for="AL">AL:</label><br>
            <input class="Teams" type="checkbox" name="teamNameCheckbox[]" value="BAL">BAL</input>
            <input class="Teams" type="checkbox" name="teamNameCheckbox[]" value="BOS">BOS</input>
            <input class="Teams" type="checkbox" name="teamNameCheckbox[]" value="CWS">CWS</input>
            <input class="Teams" type="checkbox" name="teamNameCheckbox[]" value="CLE">CLE</input>
            <input class="Teams" type="checkbox" name="teamNameCheckbox[]" value="DET">DET</input>
            <input class="Teams" type="checkbox" name="teamNameCheckbox[]" value="HOU">HOU</input>
            <input class="Teams" type="checkbox" name="teamNameCheckbox[]" value="KC">KC</input>
            <input class="Teams" type="checkbox" name="teamNameCheckbox[]" value="LAA">LAA</input><br>
            <input class="Teams" type="checkbox" name="teamNameCheckbox[]" value="MIN">MIN</input>
            <input class="Teams" type="checkbox" name="teamNameCheckbox[]" value="NYY">NYY</input>
            <input class="Teams" type="checkbox" name="teamNameCheckbox[]" value="OAK">OAK</input>
            <input class="Teams" type="checkbox" name="teamNameCheckbox[]" value="SEA">SEA</input>
            <input class="Teams" type="checkbox" name="teamNameCheckbox[]" value="TB">TB</input>
            <input class="Teams" type="checkbox" name="teamNameCheckbox[]" value="TEX">TEX</input>
            <input class="Teams" type="checkbox" name="teamNameCheckbox[]" value="TOR">TOR</input><br><br>
            <label class="Teams" for="NL">NL:</label><br>
            <input class="Teams" type="checkbox" name="teamNameCheckbox[]" value="ARI">ARI</input>
            <input class="Teams" type="checkbox" name="teamNameCheckbox[]" value="ATL">ATL</input>
            <input class="Teams" type="checkbox" name="teamNameCheckbox[]" value="CHC">CHC</input>
            <input class="Teams" type="checkbox" name="teamNameCheckbox[]" value="CIN">CIN</input>
            <input class="Teams" type="checkbox" name="teamNameCheckbox[]" value="COL">COL</input>
            <input class="Teams" type="checkbox" name="teamNameCheckbox[]" value="LAD">LAD</input>
            <input class="Teams" type="checkbox" name="teamNameCheckbox[]" value="MIA">MIA</input>
            <input class="Teams" type="checkbox" name="teamNameCheckbox[]" value="MIL">MIL</input><br>
            <input class="Teams" type="checkbox" name="teamNameCheckbox[]" value="NYM">NYM</input>
            <input class="Teams" type="checkbox" name="teamNameCheckbox[]" value="PHI">PHI</input>
            <input class="Teams" type="checkbox" name="teamNameCheckbox[]" value="PIT">PIT</input>
            <input class="Teams" type="checkbox" name="teamNameCheckbox[]" value="SD">SD</input>
            <input class="Teams" type="checkbox" name="teamNameCheckbox[]" value="SF">SF</input>
            <input class="Teams" type="checkbox" name="teamNameCheckbox[]" value="STL">STL</input>
            <input class="Teams" type="checkbox" name="teamNameCheckbox[]" value="WAS">WSH</input><br><br>
            <input class="submitbutton" type="submit" name="personalinfosubmit" value="Save" />
        </form>
    </div>'; 
 
    return $TeamPreferences;
}
//Create array of favorite teams.
function create_favorite_teams_array(){
    $teamCount = 0;
    if ($_POST['teamNameCheckbox']){
        $teamPreferences = json_encode($_POST['teamNameCheckbox']);
    }
    else {
        //This is not good.  Figure out what to do if person doesn't click a team.
        $teamPreferences = "NA";
    }
    
    return $teamPreferences;
}
//Save the team preferences to the DB
function save_team_preferences_to_db($teamPreferencesArray){
    global $wpdb;
    $user_ID = get_current_user_id();
    
    $checkIfUserExists = $wpdb->query("SELECT user_ID FROM `user_info` WHERE user_ID = '$user_ID'");
    
    if ($checkIfUserExists == 1){
        $update_team_preferences_to_db = "UPDATE `jwerkau17_wp1`.`user_info` SET `Team_Preferences` = '$teamPreferencesArray' WHERE `user_info`.`User_ID` = $user_ID";
        $wpdb->query($update_team_preferences_to_db);
        $wpdb->show_errors();
    }
    else{
        $add_team_preferences_to_db = "INSERT INTO `jwerkau17_wp1`.`user_info` (`Table_ID`, `User_ID`, `Team_Preferences`) VALUES (NULL, '$user_ID', '$teamPreferencesArray')";
        $wpdb->query($add_team_preferences_to_db);
        $wpdb->show_errors();
    }

}
//--------------------------------------------------------
//Need to add this to ($_POST['personalinfosubmit']).  I may need to move the posts to below and pass in
//the values.  From there I can compare and update.  I don't think posts will work for this I may just need
//to get values and then compare.
function save_new_user_attributes ($newfirstname, $newlastname, $newusername){
    global $wpdb;

    $current_user = wp_get_current_user();
    $user_ID = $current_user->ID;
    
    $StoredFirstname = get_user_meta($current_user->ID, 'first_name', true);
    $StoredLastname = get_user_meta($current_user->ID, 'last_name', true);
    $StoredUsername = $current_user->user_login;
    
    //Make sure the user is in the table to update
    $checkIfUserExists = $wpdb->query("SELECT user_ID FROM `user_info` WHERE user_ID = '$user_ID'");
    
    if ($checkIfUserExists == 1 ){
        if ($newfirstname != $StoredFirstname) {
            $update_firstname_to_db = "UPDATE jwerkau17_wp1.wp_usermeta SET meta_value = '$newfirstname' WHERE wp_usermeta.user_id = $user_ID AND wp_usermeta.meta_key = 'first_name'";
            $wpdb->query($update_firstname_to_db);
        }
        else {
            //Put warning message here that values are the same.
        }
        if ($newlastname != $StoredLastname) {
            $wpdb->query("UPDATE 'jwerkau17_wp1'.'wp_users' SET 'user_login' = $newusername WHERE 'ID' = $user_ID");
        }
        else {
            //Put warning message here that values are the same.
        }  
        if ($newusername != $StoredUsername) {
            $wpdb->query("UPDATE 'jwerkau17_wp1'.'wp_users' SET 'user_login' = $newusername WHERE 'ID' = $user_ID");
        }
        else {
            //Put warning message here that values are the same.
        } 
        display_team_preferences();
    }
    else{
        //Yeah it should not get to here so if it does...ooops.  I still need to test this though
        $update_error =  "This is awkward.  It seems as though you do not exists and I am not sure how that happened.";
    }
    return $update_error;
}
//Show AB total shortcode.
function display_AB_total( ){
    //Get total AB's from DB for user.
    $ABtotal = get_total_ABs_for_user(); 

    $ABdisplay = 
    '<div id="ABdisplay">
        <label class="greenlabel">AB: </label>  
        <label class="ABNumberShow">'. $ABtotal .'</label>
    </div>';
    return $ABdisplay;
}
//Show hot/cold legeng
function display_hot_cold_legend() {
    $hotcolddisplay =
        '<div id="hotcoldlegend">
            <p id="hotlegendlabel" class="legendlabels">HOT  </p>
            <div id="hot1legend" class="legenddisplay Hot1"></div>
            <div id="hot2legend" class="legenddisplay Hot2"></div>
            <div id="hot3legend" class="legenddisplay Hot3"></div>
            <div id="neutrallegend" class="legenddisplay Neutral"></div>
            <div id="cold3legend" class="legenddisplay Cold3"></div>
            <div id="cold2legend" class="legenddisplay Cold2"></div>
            <div id="cold1legend" class="legenddisplay Cold1"></div>
            <p id="coldlegendlabel" class="legendlabels">COLD  </p>
         </div>';
    return $hotcolddisplay;           
}
//Display template map
function display_template_map () {
    $locationcolor = Neutral;
    $templatestrikezone = 
      '<div id="TemplateStrikeZoneMap">
          <div id="Location1" class="StrikeZoneStyle Hot1"></div>
          <div id="Location2" class="StrikeZoneStyle Neutral"></div>
          <div id="Location3" class="StrikeZoneStyle Cold1"></div><br/>
          <div id="Location4" class="StrikeZoneStyle Hot2"></div>
          <div id="Location5" class="StrikeZoneStyle Neutral"></div>
          <div id="Location6" class="StrikeZoneStyle Cold2"></div><br/>
          <div id="Location7" class="StrikeZoneStyle Hot3"></div>
          <div id="Location8" class="StrikeZoneStyle Neutral"></div>
          <div id="Location9" class="StrikeZoneStyle Cold3"></div>      
       </div>';   
    return $templatestrikezone;
}
//Display hitting locations
function display_hitting_map( ) {
    $BAforlocations = calculate_hot_cold_zones();
    
    $BAlocation1 = $BAforlocations[1];
    $BAlocation2 = $BAforlocations[2];
    $BAlocation3 = $BAforlocations[3];
    $BAlocation4 = $BAforlocations[4];
    $BAlocation5 = $BAforlocations[5];
    $BAlocation6 = $BAforlocations[6];
    $BAlocation7 = $BAforlocations[7];
    $BAlocation8 = $BAforlocations[8];
    $BAlocation9 = $BAforlocations[9];
    
    for ($location = 1; $location < 10; $location++){
        if ($BAforlocations[$location] == 0) {
            $locationcolor = Neutral;
        }
        else if ($BAforlocations[$location] <= .150) {
            $locationcolor = Cold1;
        }
        else if ($BAforlocations[$location] <= .200) {
            $locationcolor = Cold2;
        }
        else if ($BAforlocations[$location] <= .250) {
            $locationcolor = Cold3;
        }
        else if ($BAforlocations[$location] <= .270) { 
            $locationcolor = Neutral;
        }
        else if ($BAforlocations[$location] <= .300) {
            $locationcolor = Hot3;
        }
        else if ($BAforlocations[$location] <= .350) {
            $locationcolor = Hot2;
        }
        else if ($BAforlocations[$location] >= .350) {
            $locationcolor = Hot1;
        }
        else {
            $locationcolor = Neutral;
        }
        
        if ($location == 1) {
            $location1color = $locationcolor;
        }
        else if ($location == 2) {
            $location2color = $locationcolor;
        }
        else if ($location == 3) {
            $location3color = $locationcolor;
        }
        else if ($location == 4) {
            $location4color = $locationcolor;
        }
        else if ($location == 5) {
            $location5color = $locationcolor;
        }
        else if ($location == 6) {
            $location6color = $locationcolor;
        }
        else if ($location == 7) {
            $location7color = $locationcolor;
        }
        else if ($location == 8) {
            $location8color = $locationcolor;
        }
        else if ($location == 9) {
            $location9color = $locationcolor;
        }
    }
    
    $hittinglocations = 
      '<div id="OffensiveStrikeZoneMap">
          <div id="Location1" class="StrikeZoneStyle '.$location1color.'"><span id="one">1<br>'.$BAlocation1.'</span></div>
          <div id="Location2" class="StrikeZoneStyle '.$location2color.'"><span id="two">2<br>'.$BAlocation2.'</span></div>
          <div id="Location3" class="StrikeZoneStyle '.$location3color.'"><span id="three">3<br>'.$BAlocation3.'</span></div><br/>
          <div id="Location4" class="StrikeZoneStyle '.$location4color.'"><span id="four">4<br>'.$BAlocation4.'</span></div>
          <div id="Location5" class="StrikeZoneStyle '.$location5color.'"><span id="five">5<br>'.$BAlocation5.'</span></div>
          <div id="Location6" class="StrikeZoneStyle '.$location6color.'"><span id="six">6<br>'.$BAlocation6.'</span></div><br/>
          <div id="Location7" class="StrikeZoneStyle '.$location7color.'"><span id="seven">7<br>'.$BAlocation7.'</span></div>
          <div id="Location8" class="StrikeZoneStyle '.$location8color.'"><span id="eight">8<br>'.$BAlocation8.'</span></div>
          <div id="Location9" class="StrikeZoneStyle '.$location9color.'"><span id="nine">9<br>'.$BAlocation9.'</span></div>      
       </div>';   
    return $hittinglocations;    
}
//Display hitting statistics     
function display_hitting_statistics() {
    $OBP = calculate_OBP ();
    $SLG = calculate_SLG();
    $OPS = calculate_OPS();
    $wOBP = calculate_wOBP();
    $BABIP = calculate_BABIP();
    $FBpercent = calculate_FB_hit_percent();
    $CHpercent = calculate_CH_hit_percent();
    $CBpercent = calculate_CB_hit_percent();
    $SLpercent = calculate_SL_hit_percent();
    $OTpercent = calculate_OT_hit_percent();
    $strikeoutpercent = calculate_strikeout_percent();
    $walkpercent = calculate_walk_percent();
    $ISO = calculate_ISO();
    
    $hittingstatistics = 
        '<div id="hittingstatistics">
            <div class="row1">
                <div id="OBP"><span class="greenlabel">OBP:</span> '.$OBP.'    </div>
                <div id="wOBP"><span class="greenlabel">wOBP:</span> '.$wOBP.'    </div>
                <div id="FB"><span class="greenlabel">FB:</span> '.$FBpercent.'    </div>
            </div><br>
            <div class="row2">
                <div id="SLG"><span class="greenlabel">SLG:</span> '.$SLG.'    </div>
                <div id="wRC"><span class="greenlabel">wRC:</span> N/A    </div>
                <div id="CH"><span class="greenlabel">CH:</span> '.$CHpercent.'    </div>
            </div><br>
            <div class="row3">
                <div id="OPS"><span class="greenlabel">OPS:</span> '.$OPS.'    </div>
                <div id="BABIP"><span class="greenlabel">BABIP:</span> '.$BABIP.'    </div>
                <div id="CB"><span class="greenlabel">CB:</span> '.$CBpercent.'    </div>
            </div><br>
            <div class="row4">
                <div id="ISO"><span class="greenlabel">ISO:</span> '.$ISO.'    </div>
                <div id="BlankLabel1"></div>
                <div id="SL"><span class="greenlabel">SL:</span> '.$SLpercent.'    </div>
            </div><br>
            <div class="row5">
                <div id="BlankLabel2"></div>
                <div id="BlankLabel3"></div>
                <div id="OT"><span class="greenlabel">OT:</span> '.$OTpercent.'    </div>
            </div><br>
            <div class="row6">
                <div id="BlankLabel4"></div>
                <div id="BlankLabel5"></div>
                <div id="K"><span class="greenlabel">K:</span> '.$strikeoutpercent.'    </div>
            </div><br>
            <div class="row7">
                <div id="BlankLabel6"></div>
                <div id="BlankLabel7"></div>
                <div id="BB"><span class="greenlabel">BB:</span> '.$walkpercent.'    </div> 
            </div></br>
         </div>';
    return $hittingstatistics;
}
//Display results buttons.
function display_results_options () {
    $resultsoptions = 
    '<div id="clearboth">
     </div>
     <form action="" method="post">
        <div id="PitchLocationAndTypeDropdown">
            <label id="LocationLabel" for="LocationLabel">Pitch Location: </label>
            <select name="PitchLocationDropDown">
                <option value="1">1</option>
                <option value="2">2</option>
                <option value="3">3</option>
                <option value="4">4</option>
                <option value="5">5</option>
                <option value="6">6</option>
                <option value="7">7</option>
                <option value="8">8</option>
                <option value="9">9</option>
                <option value="10">BB</option>
            </select>  
            <label id="TypeLabel" for="TypeLabel">Pitch Type: </label>
            <select name="PitchTypeDropDown">
                <option value="1">FB</option>
                <option value="2">CH</option>
                <option value="3">CB</option>
                <option value="4">SL</option>
                <option value="5">OTHER</option>
            </select>
        </div>
        <div id="OutResultsLabel">
            <br><label for="OutResultsLabel">Outs:</label><br><br>
        </div>
        <div id="OutResultsbuttons">
            <input id="strikeoutbutton" class="offensivebuttons" type="submit" name="strikeout" value="K" />
            <input id="groundoutbutton" class="offensivebuttons" type="submit" name="groundout" value="GO" /> 
            <input id="flyoutoutbutton" class="offensivebuttons" type="submit" name="flyout" value="FO" /> 
            <input id="sacflybutton" class="offensivebuttons" type="submit" name="sacfly" value="Sac Fly" />
        </div><br>
        <div id="OnBaseResultsLabel">
            <label for="OnBaseResultsLabel">On Base:</label><br><br>
        </div>
        <div id="OnBaseResultsButtons">
            <input id="errorbutton" class="offensivebuttons" type="submit" name="error" value="E" />
            <input id="walkbutton" class="offensivebuttons" type="submit" name="walk" value="BB" />
            <input id="hitbypitchbutton" class="offensivebuttons" type="submit" name="hitbypitch" value="HBP" />
            <input id="singlebutton" class="offensivebuttons" type="submit" name="1Bresult" value="1B" /> 
            <input id="doublebutton" class="offensivebuttons" type="submit" name="2Bresult" value="2B" /> 
            <input id="triplebutton" class="offensivebuttons" type="submit" name="3Bresult" value="3B" /> 
            <input id="homerunbutton" class="offensivebuttons" type="submit" name="HRresult" value="HR" />
        </div>
    </form>';
    
    return $resultsoptions;
}
//display export options
function display_export_options () {
    $ExportEditOptions = 
    '<form action="" method="post">
        <div id="DataOptionsButtons">
            <input id="exportbutton" class="exporteditbuttons" type="submit" name="export" value="export"/>
        </div>
    </form>';
    
    //Add this to the div later for editing the table
    //<input id="editbutton" class="exporteditbuttons" type="submit" name="edit" value="edit"/>
    return $ExportEditOptions;
}
//Display offensive edit table
function display_offensive_edit_table (){
    global $wpdb;
    $user_ID = get_current_user_id();

    $result = $wpdb->get_results("SELECT AtBatDate, AtBat FROM analysis_hitting WHERE User_ID = $user_ID");

      echo '<table>';
      echo "<tr><th>At Bat Date</th><th>At Bat</th></tr>";
      foreach ($result as $row) {
        // Each $row is a row from the query
        echo '<tr>';
        echo '<td>' . $row->AtBatDate . '</td>';
        echo '<td>' . $row->AtBat . '</td>';
        echo '</tr>';
      }
      echo '</table>';
}
//Grab the rows for logged in user from analysis_hitting table
function get_total_ABs_for_user () {
    global $wpdb;
    $user_ID = get_current_user_id();
    
    //Grab AB column from DB
    $wpdb->get_col("SELECT AtBat FROM `analysis_hitting` WHERE user_ID = $user_ID");
    
    //Counts the number of rows in AB
    $plateappearence = $wpdb->num_rows + 1;

    return $plateappearence;
}
//Calculate hot and cold zones for hitter
function calculate_hot_cold_zones () {
    global $wpdb;
    $user_ID = get_current_user_id();
    
    for ($location = 1; $location < 10; $location++) {
        $sacflysum = 0;
        $walksum = 0;
        $hitbypitchsum = 0;
        $locationhits = 0;
        
        $usertable = $wpdb->get_results("SELECT PitchLocation, SacFly, Error, Walk, HitByPitch, SingleHit, DoubleHit, TripleHit, HomeRun FROM `analysis_hitting` WHERE User_ID = $user_ID AND PitchLocation = $location");
        
        foreach ($usertable as $row) {
            $sacflysum += $row->SacFly;
            $walksum += $row->Walk;
            $hitbypitchsum += $row->HitByPitch;

            $locationhits += $row->SingleHit + $row->DoubleHit + $row->TripleHit + $row->HomeRun;
        }
        $plateappearencesforlocation = count($usertable);
        $atbatsforlocation = $plateappearencesforlocation - ($sacflysum + $walksum + $hitbypitchsum);
        
        if ($atbatsforlocation != 0){
            $BAforlocations[$location] = round($locationhits/$atbatsforlocation,3);
            if ($BAforlocations == 0) {
                $BAforlocations[$location] = "N/A";
            }
        }
        else {
            $BAforlocations[$location] = "N/A";
        }
        
    }
    return $BAforlocations;
}
//Calculate OBP
function calculate_OBP () {
    global $wpdb;
    $user_ID = get_current_user_id();
    
    //Grab user on base data from DB
    $usertable = $wpdb->get_results("SELECT Error, Walk, HitByPitch, SingleHit, DoubleHit, TripleHit, HomeRun FROM `analysis_hitting` WHERE User_ID = $user_ID");

    foreach ($usertable as $row) {
        $onbasesum += $row->Error + $row->Walk + $row->HitByPitch + $row->SingleHit + $row->DoubleHit + $row->TripleHit + $row->HomeRun;
    }
    $plateappearences = count($usertable);
    
    if ($plateappearences != 0) {
        $OBP = round(($onbasesum/$plateappearences), 4);
    }
    else {
        $OBP = 0;
    }

    return $OBP;
}
//Calculate slugging percentage.
function calculate_SLG() {
    global $wpdb;
    $user_ID = get_current_user_id();
    
    //Grab user hit data from DB
    $usertable = $wpdb->get_results("SELECT SacFly, Walk, HitByPitch, SingleHit, DoubleHit, TripleHit, HomeRun FROM `analysis_hitting` WHERE User_ID = $user_ID");
    
    foreach ($usertable as $row) {
        $sacflysum += $row->SacFly;
        $walksum += $row->Walk;
        $hitbypitchsum += $row->HitByPitch;
        $singlesum += $row->SingleHit;
        $doublesum += $row->DoubleHit;
        $triplesum += $row->TripleHit;
        $homerunsum += $row->HomeRun;
    }
    $plateappearences = count($usertable);
    $atbats = $plateappearences - ($sacflysum + $walksum + $hitbypitchsum);
    
    if ($atbats != 0) {
        $SLG = round(($singlesum + (2*$doublesum) + (3*$triplesum) + (4*$homerunsum))/$atbats, 4);
    }
    else {
        $SLG = 0;
    }

    return $SLG;
}
//Calculate OPS.
function calculate_OPS() {
    $OBP = calculate_OBP();
    $SLG = calculate_SLG();
    
    $OPS = $OBP + $SLG;
    
    return $OPS;
}
//Calculate wOBP
function calculate_wOBP () {
    global $wpdb;
    $user_ID = get_current_user_id();
    
    //Grab user hit data from DB
    $usertable = $wpdb->get_results("SELECT SacFly, Walk, HitByPitch, SingleHit, DoubleHit, TripleHit, HomeRun FROM `analysis_hitting` WHERE User_ID = $user_ID");
    
    foreach ($usertable as $row) {
        $walksum += $row->Walk;
        $hitbypitchsum += $row->HitByPitch;
        $singlesum += $row->SingleHit;
        $doublesum += $row->DoubleHit;
        $triplesum += $row->TripleHit;
        $homerunsum += $row->HomeRun;
    }
    $plateappearences = count($usertable);
    
    if ($plateappearences != 0){
        $wOBP = round(((.691*$walksum) + (.722*$hitbypitchsum) + (.884*$singlesum) + (1.257*$doublesum) + (1.593*$triplesum) + (2.058*$homerunsum))/$plateappearences, 4);
    }
    else {
        $wOBP = 0;
    }
    
    return $wOBP;
}
//Calculate BA on balls in play
function calculate_BABIP () {
    global $wpdb;
    $user_ID = get_current_user_id();
    
    //Grab user hit data from DB
    $usertable = $wpdb->get_results("SELECT SacFly, Walk, HitByPitch, SingleHit, DoubleHit, TripleHit, HomeRun FROM `analysis_hitting` WHERE User_ID = $user_ID");
    
    foreach ($usertable as $row) {
        $strikeoutsum = $row->StrikeOut;
        $walksum += $row->Walk;
        $hitbypitchsum += $row->HitByPitch;
        $singlesum += $row->SingleHit;
        $doublesum += $row->DoubleHit;
        $triplesum += $row->TripleHit;
        $homerunsum += $row->HomeRun;
    }
    $plateappearences = count($usertable);
    $atbatswithballsinplay = $plateappearences - ($strikeoutsum + $walksum + $hitbypitchsum + $homerunsum);
    $hitsminushomerun = $singlesum + $doublesum + $triplesum;
    
    if ($atbatswithballsinplay != 0) {
        $BABIP = round($hitsminushomerun/$atbatswithballsinplay,4);
    }
    else {
        $BABIP = 0;
    }
    
    return $BABIP;
}
//Calculate ISO
function calculate_ISO() {
    global $wpdb;
    $user_ID = get_current_user_id();
    
    //Grab user hit data from DB
    $usertable = $wpdb->get_results("SELECT SacFly, Walk, HitByPitch, SingleHit, DoubleHit, TripleHit, HomeRun FROM `analysis_hitting` WHERE User_ID = $user_ID");
    
    foreach ($usertable as $row) {
        $sacflysum += $row->SacFly;
        $walksum += $row->Walk;
        $hitbypitchsum += $row->HitByPitch;
        $doublesum += $row->DoubleHit;
        $triplesum += $row->TripleHit;
        $homerunsum += $row->HomeRun;
    }
    $plateappearences = count($usertable);
    $atbats = $plateappearences - ($sacflysum + $walksum + $hitbypitchsum);
    
    if ($atbats != 0) {
        $ISO = round(($doublesum + (2*$triplesum) + (3*$homerunsum))/$atbats, 4);
    }
    else {
        $ISO = 0;
    }
    
    return $ISO;
}
//Calculate FB hit percentage
function calculate_FB_hit_percent() {
    global $wpdb;
    $user_ID = get_current_user_id();
    
    //Grab user on base data from DB
    $usertable = $wpdb->get_results("SELECT PitchType, SingleHit, DoubleHit, TripleHit, HomeRun FROM `analysis_hitting` WHERE User_ID = $user_ID");

    foreach ($usertable as $row) {
        $singlesum += $row->SingleHit;
        $doublesum += $row->DoubleHit;
        $triplesum += $row->TripleHit;
        $homerunsum += $row->HomeRun;
        
        if ($row->PitchType == 1) {
            if ($row->SingleHit || $row->DoubleHit || $row->TripleHit || $row->HomeRun) {
                $hitsonFB += 1;
            }
        }
    }
    $totalhits = $singlesum + $doublesum + $triplesum + $homerunsum;  
    
    if ($totalhits != 0) {
        $FBhitpercent = round($hitsonFB/$totalhits, 4);
    }
    else {
        $FBhitpercent = 0;
    }
        
    return $FBhitpercent;
}
//Calculate CH hit percentage
function calculate_CH_hit_percent() {
    global $wpdb;
    $user_ID = get_current_user_id();
    
    //Grab user on base data from DB
    $usertable = $wpdb->get_results("SELECT PitchType, SingleHit, DoubleHit, TripleHit, HomeRun FROM `analysis_hitting` WHERE User_ID = $user_ID");

    foreach ($usertable as $row) {
        $singlesum += $row->SingleHit;
        $doublesum += $row->DoubleHit;
        $triplesum += $row->TripleHit;
        $homerunsum += $row->HomeRun;
        
        if ($row->PitchType == 2) {
            if ($row->SingleHit || $row->DoubleHit || $row->TripleHit || $row->HomeRun) {
                $hitsonCH += 1;
            }
        }
    }
    $totalhits = $singlesum + $doublesum + $triplesum + $homerunsum;
    
    if ($totalhits != 0) {
        $CHhitpercent = round($hitsonCH/$totalhits, 4);
    }
    else {
        $CHhitpercent = 0;
    }
        
    return $CHhitpercent;
}
//Calculate CB hit percentage
function calculate_CB_hit_percent() {
    global $wpdb;
    $user_ID = get_current_user_id();
    
    //Grab user on base data from DB
    $usertable = $wpdb->get_results("SELECT PitchType, SingleHit, DoubleHit, TripleHit, HomeRun FROM `analysis_hitting` WHERE User_ID = $user_ID");

    foreach ($usertable as $row) {
        $singlesum += $row->SingleHit;
        $doublesum += $row->DoubleHit;
        $triplesum += $row->TripleHit;
        $homerunsum += $row->HomeRun;
        
        if ($row->PitchType == 3) {
            if ($row->SingleHit || $row->DoubleHit || $row->TripleHit || $row->HomeRun) {
                $hitsonCB += 1;
            }
        }
    }
    $totalhits = $singlesum + $doublesum + $triplesum + $homerunsum;
    
    if ($totalhits != 0) {
        $CBhitpercent = round($hitsonCB/$totalhits, 4);
    }
    else {
        $CBhitpercent = 0;
    }
        
    return $CBhitpercent;
}
//Calculate SL hit percentage
function calculate_SL_hit_percent() {
    global $wpdb;
    $user_ID = get_current_user_id();
    
    //Grab user on base data from DB
    $usertable = $wpdb->get_results("SELECT PitchType, SingleHit, DoubleHit, TripleHit, HomeRun FROM `analysis_hitting` WHERE User_ID = $user_ID");

    foreach ($usertable as $row) {
        $singlesum += $row->SingleHit;
        $doublesum += $row->DoubleHit;
        $triplesum += $row->TripleHit;
        $homerunsum += $row->HomeRun;
        
        if ($row->PitchType == 4) {
            if ($row->SingleHit || $row->DoubleHit || $row->TripleHit || $row->HomeRun) {
                $hitsonSL += 1;
            }
        }
    }
    $totalhits = $singlesum + $doublesum + $triplesum + $homerunsum;
    
    if ($totalhits != 0) {
        $SLhitpercent = round($hitsonSL/$totalhits, 4);    
    }
    else {
        $SLhitpercent = 0;
    }

        
    return $SLhitpercent;
}
//Calculate OT hit percentage
function calculate_OT_hit_percent() {
    global $wpdb;
    $user_ID = get_current_user_id();
    
    //Grab user on base data from DB
    $usertable = $wpdb->get_results("SELECT PitchType, SingleHit, DoubleHit, TripleHit, HomeRun FROM `analysis_hitting` WHERE User_ID = $user_ID");

    foreach ($usertable as $row) {
        $singlesum += $row->SingleHit;
        $doublesum += $row->DoubleHit;
        $triplesum += $row->TripleHit;
        $homerunsum += $row->HomeRun;
        
        if ($row->PitchType == 5) {
            if ($row->SingleHit || $row->DoubleHit || $row->TripleHit || $row->HomeRun) {
                $hitsonOT += 1;
            }
        }
    }
    $totalhits = $singlesum + $doublesum + $triplesum + $homerunsum;
    
    if ($totalhits != 0) {
        $OThitpercent = round($hitsonOT/$totalhits, 4);
    }
    else {
        $OThitpercent = 0;
    }
        
    return $OThitpercent;
}
//Calculate strikeout percentage
function calculate_strikeout_percent() {
    global $wpdb;
    $user_ID = get_current_user_id();
    
    //Grab user on base data from DB
    $usertable = $wpdb->get_results("SELECT StrikeOut FROM `analysis_hitting` WHERE User_ID = $user_ID");

    foreach ($usertable as $row) {
        $strikeoutsum += $row->StrikeOut;
    }
    
    $plateappearances = count($usertable);
    
    if ($plateappearances != 0) {
        $strikoutpercent = round($strikeoutsum/$plateappearances, 4);
    }
    else {
        $strikoutpercent = 0;
    }
        
    return $strikoutpercent;
}
//Calculate walk percentage
function calculate_walk_percent() {
    global $wpdb;
    $user_ID = get_current_user_id();
    
    //Grab user on base data from DB
    $usertable = $wpdb->get_results("SELECT Walk FROM `analysis_hitting` WHERE User_ID = $user_ID");

    foreach ($usertable as $row) {
        $walksum += $row->Walk;
    }
    
    $plateappearances = count($usertable);
    
    if ($plateappearances != 0) {
        $walkpercent = round($walksum/$plateappearances, 4);
    }
    else {
        $walkpercent = 0;
    }
        
    return $walkpercent;
}
//Update DB hitting analysis table.
function update_hitting_analysis_table($PitchLocation, $PitchType, $ABResult) {
    global $wpdb;
    $user_ID = get_current_user_id();
    
    if ($ABResult == 0) {
        $strikeout = 1;
        $groundout = 0;
        $flyout = 0;
        $sacfly = 0;
        $error = 0;
        $walk = 0;
        $hitbypitch = 0;
        $single = 0;
        $double = 0;
        $triple = 0;
        $homerun = 0;
    }
    else if ($ABResult == 1) {
        $strikeout = 0;
        $groundout = 1;
        $flyout = 0;
        $sacfly = 0;
        $error = 0;
        $walk = 0;
        $hitbypitch = 0;
        $single = 0;
        $double = 0;
        $triple = 0;
        $homerun = 0;
    }
    else if ($ABResult == 2) {
        $strikeout = 0;
        $groundout = 0;
        $flyout = 1;
        $sacfly = 0;
        $error = 0;
        $walk = 0;
        $hitbypitch = 0;
        $single = 0;
        $double = 0;
        $triple = 0;
        $homerun = 0;
    }
    else if ($ABResult == 3) {
        $strikeout = 0;
        $groundout = 0;
        $flyout = 0;
        $sacfly = 1;
        $error = 0;
        $walk = 0;
        $hitbypitch = 0;
        $single = 0;
        $double = 0;
        $triple = 0;
        $homerun = 0;
    }
    else if ($ABResult == 4) {
        $strikeout = 0;
        $groundout = 0;
        $flyout = 0;
        $sacfly = 0;
        $error = 1;
        $walk = 0;
        $hitbypitch = 0;
        $single = 0;
        $double = 0;
        $triple = 0;
        $homerun = 0;
    }
    else if ($ABResult == 5) {
        $strikeout = 0;
        $groundout = 0;
        $flyout = 0;
        $sacfly = 0;
        $error = 0;
        $walk = 1;
        $hitbypitch = 0;
        $single = 0;
        $double = 0;
        $triple = 0;
        $homerun = 0;
    }
    else if ($ABResult == 6) {
        $strikeout = 0;
        $groundout = 0;
        $flyout = 0;
        $sacfly = 0;
        $error = 0;
        $walk = 0;
        $hitbypitch = 1;
        $single = 0;
        $double = 0;
        $triple = 0;
        $homerun = 0;
    }
    else if ($ABResult == 7) {
        $strikeout = 0;
        $groundout = 0;
        $flyout = 0;
        $sacfly = 0;
        $error = 0;
        $walk = 0;
        $hitbypitch = 0;
        $single = 1;
        $double = 0;
        $triple = 0;
        $homerun = 0;
    }
    else if ($ABResult == 8) {
        $strikeout = 0;
        $groundout = 0;
        $flyout = 0;
        $sacfly = 0;
        $error = 0;
        $walk = 0;
        $hitbypitch = 0;
        $single = 0;
        $double = 1;
        $triple = 0;
        $homerun = 0;
    }
    else if ($ABResult == 9) {
        $strikeout = 0;
        $groundout = 0;
        $flyout = 0;
        $sacfly = 0;
        $error = 0;
        $walk = 0;
        $hitbypitch = 0;
        $single = 0;
        $double = 0;
        $triple = 1;
        $homerun = 0;
    }
    else if ($ABResult == 10) {
        $strikeout = 0;
        $groundout = 0;
        $flyout = 0;
        $sacfly = 0;
        $error = 0;
        $walk = 0;
        $hitbypitch = 0;
        $single = 0;
        $double = 0;
        $triple = 0;
        $homerun = 1;
    }
    else {
        //uh oh
    }
    
    //Get total AB's from DB for user. 
    $ABtotal = get_total_ABs_for_user ();
  
    //Add new row to DB
    $addnextabtodbquery = "INSERT INTO `jwerkau17_wp1`.`analysis_hitting` (`Table_ID`, `User_ID`, `AtBat`, `PitchLocation`, `PitchType`, `StrikeOut`, `GroundOut`, `FlyOut`, `SacFly`, `Error`, `Walk`, `HitByPitch`, `SingleHit`, `DoubleHit`, `TripleHit`, `HomeRun`) VALUES (NULL, '$user_ID', '$ABtotal', '$PitchLocation', '$PitchType', '$strikeout', '$groundout', '$flyout', '$sacfly', '$error', '$walk', '$hitbypitch', '$single', '$double', '$triple', '$homerun')";
    $wpdb->query( $addnextabtodbquery);
    $wpdb->show_errors();
}
//Pitching stuff
//Display pitching image
function display_pitching_map () {
    get_XY_coordinates_from_click();
}
function get_XY_coordinates_from_click () {
    ?>
      <script type="text/javascript" src="https://www.google.com/jsapi"></script>
      <script src="https://www.google.com/uds/?file=visualization&amp;v=1&amp;packages=corechart" type="text/javascript"></script>
      <link href="https://www.google.com/uds/api/visualization/1.0/ce05bcf99b897caacb56a7105ca4b6ed/ui+en.css" type="text/css" rel="stylesheet" />
      <script src="https://www.google.com/uds/api/visualization/1.0/ce05bcf99b897caacb56a7105ca4b6ed/format+en,default+en,ui+en,corechart+en.I.js" type="text/javascript"></script>

      <script type="text/javascript">
        google.load("visualization", "1", {packages: ["corechart"]});
        var chart;
        var data1 = new google.visualization.DataTable();
        data1.addColumn("number", "Length");
        data1.addColumn("number", "Height");
        
        var userdatax = userdatay = null;
        google.setOnLoadCallback(function() {
          drawChart(data1, userdatax, userdatay);
        });
        function drawChart(data, userdatax, userdatay) {

          chart = new google.visualization.ScatterChart(document.getElementById('chart_div'));
          var options = {
            hAxis: {minValue: -2, maxValue: 2, gridlines: {color: 'transparent', count: 13}, ticks: [-2, -1.5, -1, -.5, 0, .5, 1, 1.5, 2], baseline: -2},
            vAxis: {minValue: 0, maxValue: 5, gridlines: {color: 'transparent', count: 10}, ticks: [0,.5,1,1.5,2,2.5,3,3.5,4,4.5,5]},
            'width':500,
            legend:'none',
            fontSize:12
          };
          chart.draw(data, options);
          if (userdatax !== null || userdatay !== null) {
            var xcoo = chart.getChartLayoutInterface().getHAxisValue(userdatax);
            var ycoo = chart.getChartLayoutInterface().getVAxisValue(userdatay);
            data.addRows([[xcoo, ycoo]]);
            chart.draw(data, options);
            $('#x_axis').html(xcoo);
            $('#y_axis').html(ycoo);
          }
        }
      </script>

      <script type="text/javascript" src="http://www.technicalkeeda.com/js/javascripts/plugin/jquery.js"></script>
      <script>
        $(document).ready(function() {
          $('#chart_div').click(function(e) {
            var offset = $(this).offset();
            var userdatax = e.pageX - offset.left;
            var userdatay = e.pageY - offset.top;
            drawChart(data1, userdatax, userdatay);
          });
        });
      </script>
      X Axis: <span id="x_axis">0</span>
      Y Axis: <span id="y_axis">0</span>

      <div id="chart_div"></div>
      
    <?php
}
//MAIN
//Define links for prod or dev
$DevProdFlag = "dev";
if ($DevProdFlag == "dev"){
    $analysisPage = "http://localhost/AllForWon/?page_id=128";
    $offensiveStatisticsPage = "http://localhost/AllForWon/?page_id=347 ";
    $pitchingStatisticsPage = "http://localhost/AllForWon/?page_id=355";
    $exploringStatisticsPage = "http://localhost/AllForWon/?page_id=161";
    $goodReadsPage = "http://localhost/AllForWon/?page_id=119";
    $homePage = "http://localhost/AllForWon/";
    $loginPage = "http://localhost/AllForWon/?page_id=168";
    $logoutPage = "http://localhost/AllForWon/?page_id=190";
    $profilePage = "http://localhost/AllForWon/?page_id=140";
    $registerPage = "http://localhost/AllForWon/?page_id=206";
    $registrationSuccesfullPage = "http://localhost/AllForWon/?page_id=219";
    //Need to add this to prod
    $EditOffensiveStatisticsPage = "http://localhost/AllForWon/?page_id=421";
}
else{
    $analysisPage = "http://www.allforwon.com/?page_id=128 ";
    $offensiveStatisticsPage = "http://www.allforwon.com/?page_id=347 ";
    $pitchingStatisticsPage = "http://www.allforwon.com/?page_id=355";
    $exploringStatisticsPage = "http://www.allforwon.com/?page_id=161";
    $goodReadsPage = "http://www.allforwon.com/?page_id=119";
    $homePage = "http://www.allforwon.com/";
    $loginPage = "http://www.allforwon.com/?page_id=168";
    $logoutPage = "http://www.allforwon.com/?page_id=190";
    //May need to change path for dev
    $profilePage = "http://www.allforwon.com/?page_id=140";
    $registerPage = "http://www.allforwon.com/?page_id=206";
    $registrationSuccesfullPage = "http://www.allforwon.com/?page_id=219";
}
//Register user if need be.
if ($_POST['registersubmit']) {
    global $register_error;
    $register_error = register_user();
}
//Save profile preferences
if ($_POST['personalinfosubmit']){   
    //These are not saving values from text field
    $newfirstname = $_GET['ProfileFirstName'];
    $newlastname = $_POST['ProfileLastName'];
    $newusername = $_POST['ProfileUsername'];

    echo "$newfirstname";
    
    save_new_user_attributes ($newfirstname, $newlastname, $newusername);
    $teamPreferencesArray = create_favorite_teams_array();
    save_team_preferences_to_db($teamPreferencesArray);

}
//Login user
if ($_POST['login']) {
    global $login_error;
    $login_error = login_user();
}
//Get user data and upload to DB
if ($_POST['strikeout'] || $_POST['groundout'] || $_POST['flyout'] || $_POST['sacfly'] || $_POST['error'] || $_POST['walk'] || $_POST['hitbypitch'] || $_POST['1Bresult'] || $_POST['2Bresult'] || $_POST['3Bresult'] || $_POST['HRresult']) {
    $strikeout = 0;
    $groundout = 1;
    $flyout = 2;
    $sacfly = 3;
    $error = 4;
    $walk = 5;
    $hitbypitch = 6;
    $single = 7;
    $double = 8;
    $triple = 9;
    $homerun = 10;
    if ($_POST['strikeout']) {
        $PitchLocation = $_POST['PitchLocationDropDown'];
        $PitchType = $_POST['PitchTypeDropDown'];
        update_hitting_analysis_table($PitchLocation, $PitchType, $strikeout);
    }
    else if ($_POST['groundout']) {
        $PitchLocation = $_POST['PitchLocationDropDown'];
        $PitchType = $_POST['PitchTypeDropDown'];
        update_hitting_analysis_table($PitchLocation, $PitchType, $groundout);
    }
    else if ($_POST['flyout']) {
        $PitchLocation = $_POST['PitchLocationDropDown'];
        $PitchType = $_POST['PitchTypeDropDown'];
        update_hitting_analysis_table($PitchLocation, $PitchType, $flyout);
    }
    else if ($_POST['sacfly']) {
        $PitchLocation = $_POST['PitchLocationDropDown'];
        $PitchType = $_POST['PitchTypeDropDown'];
        update_hitting_analysis_table($PitchLocation, $PitchType, $sacfly);
    }
    else if ($_POST['error']) {
        $PitchLocation = $_POST['PitchLocationDropDown'];
        $PitchType = $_POST['PitchTypeDropDown'];
        update_hitting_analysis_table($PitchLocation, $PitchType, $error);
    }
    else if ($_POST['walk']) {
        $PitchLocation = $_POST['PitchLocationDropDown'];
        $PitchType = $_POST['PitchTypeDropDown'];
        update_hitting_analysis_table($PitchLocation, $PitchType, $walk);
    }
    else if ($_POST['hitbypitch']) {
        $PitchLocation = $_POST['PitchLocationDropDown'];
        $PitchType = $_POST['PitchTypeDropDown'];
        update_hitting_analysis_table($PitchLocation, $PitchType, $hitbypitch);
    }
    else if ($_POST['1Bresult']) {
        $PitchLocation = $_POST['PitchLocationDropDown'];
        $PitchType = $_POST['PitchTypeDropDown'];
        update_hitting_analysis_table($PitchLocation, $PitchType, $single);
    }
    else if ($_POST['2Bresult']) {
        $PitchLocation = $_POST['PitchLocationDropDown'];
        $PitchType = $_POST['PitchTypeDropDown'];
        update_hitting_analysis_table($PitchLocation, $PitchType, $double);
    }
    else if ($_POST['3Bresult']) {
        $PitchLocation = $_POST['PitchLocationDropDown'];
        $PitchType = $_POST['PitchTypeDropDown'];
        update_hitting_analysis_table($PitchLocation, $PitchType, $triple);
    }
    else if ($_POST['HRresult']) {
        $PitchLocation = $_POST['PitchLocationDropDown'];
        $PitchType = $_POST['PitchTypeDropDown'];
        update_hitting_analysis_table($PitchLocation, $PitchType, $homerun);
    }
}
if ($_POST['export']){
$wpdb->show_errors(); 
global $wpdb;
$user_ID = get_current_user_id();
						
    // Build your query						
    $MyQuery = $wpdb->get_results("SELECT AtBatDate, AtBat, PitchLocation, PitchType, StrikeOut, GroundOut, FlyOut, SacFly, Error, Walk, HitByPitch, SingleHit, DoubleHit, TripleHit, HomeRun FROM analysis_hitting
    WHERE User_ID = $user_ID");
    

    //Check to make sure there are results.
    if ($wpdb->num_rows == 0) {
        $Error = "No offensive statistics on record.";
      die("The following error was found: $Error");
    }
    //Check for other errors.
    elseif (!$MyQuery) {
        $Error = $wpdb->print_error();
      die("The following error was found: $Error");
    }
    //Export data.
    else {
        // Set header row values
        $csv_fields=array(1 => "AtBatDate", 2 => "AtBat", 3 => "PitchLocation", 4 => "PitchType", 5 => "StrikeOut", 6 => "GroundOut", 7 => "FlyOut", 8 => "SacFly", 9 => "Error", 10 => "Walk", 11 => "HitByPitch", 12 => "SingleHit", 13 => "DoubleHit", 14 => "TripleHit", 15 => "HomeRun");

        $output_filename = 'Offensive Statistics' . '.csv';
        $output_handle = @fopen( 'php://output', 'w' );

        header( 'Cache-Control: must-revalidate, post-check=0, pre-check=0' );
        header( 'Content-Description: File Transfer' );
        header( 'Content-type: text/csv' );
        header( 'Content-Disposition: attachment; filename=' . $output_filename );
        header( 'Expires: 0' );
        header( 'Pragma: public' );	

        // Insert header row
        fputcsv( $output_handle, $csv_fields );

        // Parse results to csv format
        foreach ($MyQuery as $Result) {
                $leadArray = (array) $Result; // Cast the Object to an array
                // Add row to file
                fputcsv( $output_handle, $leadArray );
                }

        // Close output file stream
        fclose( $output_handle ); 

        die();
    }
}
//Will use this to edit table in the future
/*if ($_POST['edit']){
    global $EditOffensiveStatisticsPage;
    wp_redirect($EditOffensiveStatisticsPage);
    exit;
}*/



