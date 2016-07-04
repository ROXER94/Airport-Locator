<!DOCTYPE html>
<!--See this website live here: http://info230.cs.cornell.edu/users/rdo26sp15/www/world/Moat/index.php -->
<html lang="en-US">

<head>
    <meta charset="utf-8">
    <title>Moat - Airport Locator</title>

    <!-- Using jQuery with a CDN -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.2/jquery.min.js"></script>

    <!-- JS file -->
    <script src="resources/jquery.easy-autocomplete.min.js"></script>

    <!-- CSS files -->
    <link rel="stylesheet" href="resources/easy-autocomplete.min.css">

    <link rel="stylesheet" type="text/css" href="css/main.css">
    <link href="https://fonts.googleapis.com/css?family=Sriracha" rel="stylesheet">

</head>

<body>

    <h2> Calculate the distance between any two U.S. airports! </h2>
    <h3> And visualize the travel path! </h3>

    <form action="index.php" method="post">
    <div id="left">
        <span class="input">Source</span>
        <input type="text" class="airports" id="airport1" name="airport1" placeholder="Airport 1" />
    </div>
    <input type="submit" name="submit" class="styled-button" value="Compute!" />
    <div id="right">
        <span class="input">Destination</span>
        <input type="text" class="airports" id="airport2" name="airport2" placeholder="Airport 2" />
    </div>
    </form>

    <div id="map" ></div>

    <?php

require_once "config.php";
$mysqli1 = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
$mysqli2 = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

if (mysqli_connect_error()) {
    die("Can't connect to database: " . $mysqli1->error);
    die("Can't connect to database: " . $mysqli2->error);
}

if (isset($_POST['submit'])) {
    $airport1 = filter_input(INPUT_POST, 'airport1', FILTER_SANITIZE_STRING);
    $airport2 = filter_input(INPUT_POST, 'airport2', FILTER_SANITIZE_STRING);

    if (!empty($airport1) && !empty($airport2)) {
        $query1 = "SELECT * FROM Airports WHERE Name = '$airport1'";
        $query2 = "SELECT * FROM Airports WHERE Name = '$airport2'";

        $result1 = $mysqli1->query($query1);
        $result2 = $mysqli2->query($query2);

        if ($result1 && $result1->num_rows == 1 && $result2 && $result2->num_rows == 1) {

            $array1 = $result1->fetch_assoc();
            $array2 = $result2->fetch_assoc();

            //Airport1 Data
            $airport1Name    = $array1['Name'];
            $airport1City    = $array1['City'];
            $airport1Country = $array1['Country'];
            $airport1FAA     = $array1['FAA'];
            $airport1LAT     = $array1['Latitude'];
            $airport1LONG    = $array1['Longitude'];

            //Airport2 Data
            $airport2Name    = $array2['Name'];
            $airport2City    = $array2['City'];
            $airport2Country = $array2['Country'];
            $airport2FAA     = $array2['FAA'];
            $airport2LAT     = $array2['Latitude'];
            $airport2LONG    = $array2['Longitude'];

            if ($airport1Name != $airport2Name) {
                echo "<div id='output'><b>Source</b>: " . $airport1Name . " (" . $airport1FAA . ") - " . $airport1City . ", U.S.";
                echo "<br /><b>Latitude</b>: " . $airport1LAT . ", <b>Longitude</b>: " . $airport1LONG;
                echo "<br /><b>Destination</b>: " . $airport2Name . " (" . $airport2FAA . ") - " . $airport2City . ", U.S.";
                echo "<br /><b>Latitude</b>: " . $airport2LAT . ", <b>Longitude</b>: " . $airport2LONG;

                //**Calculations**//

                //Constants
                $R        = 6373; //Earth mean radius
                $km_to_nm = 0.539957; //Convert 1 kilometer to nautical miles

                //Converting to radians
                $airport1LAT_Radian  = $airport1LAT * M_PI / 180;
                $airport1LONG_Radian = $airport1LONG * M_PI / 180;
                $airport2LAT_Radian  = $airport2LAT * M_PI / 180;
                $airport2LONG_Radian = $airport2LONG * M_PI / 180;

                //Calculating distance between the latitude and longitute coordinates
                $LAT  = $airport2LAT - $airport1LAT;
                $LAT  = $LAT * M_PI / 180;
                $LONG = $airport2LONG - $airport1LONG;
                $LONG = $LONG * M_PI / 180;

                //Haversine Formula - http://www.movable-type.co.uk/scripts/latlong.html
                $a = sin($LAT / 2) * sin($LAT / 2) + cos($airport1LAT_Radian) * cos($airport2LAT_Radian) * sin($LONG / 2) * sin($LONG / 2);
                $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

                //Rounding
                $nmiles = $R * $c * $km_to_nm;
                $nmiles = round($nmiles, 4);


                echo "<br /> The distance between " . $airport1Name . " and " . $airport2Name . " is <b>" . $nmiles . " nautical miles</b></div>";

	?>

      <!--Google Maps-->
      <script>

      function initMap() {

        //Access PHP variables in JavaScript
        var airport1LAT = "<?php echo $airport1LAT;?>";
        var airport1LONG = "<?php echo $airport1LONG;?>";
        var airport2LAT = "<?php echo $airport2LAT;?>";
        var airport2LONG = "<?php echo $airport2LONG;?>";

        //Convert String values to Floating Point number
        var airport1LAT = parseFloat(airport1LAT);
        var airport1LONG = parseFloat(airport1LONG);
        var airport2LAT = parseFloat(airport2LAT);
        var airport2LONG = parseFloat(airport2LONG);

        var map = new google.maps.Map(document.getElementById('map'), {
          zoom: 4,
          //Coordinates for the center of the U.S.
          center: {lat: 39.8282, lng: -98.5795},
          mapTypeId: google.maps.MapTypeId.TERRAIN
        });

        var flightPlanCoordinates = [
          {lat: airport1LAT, lng: airport1LONG},
          {lat: airport2LAT, lng: airport2LONG},
        ];
        var flightPath = new google.maps.Polyline({
          path: flightPlanCoordinates,
          strokeColor: '#FF0000',
          strokeOpacity: 1.0,
          strokeWeight: 2
        });

        flightPath.setMap(map);
      }
    </script>

    <script async defer
    src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBhbL-Lx8MzYqJIr7z1A5lPHVJWzDGKDkQ&callback=initMap">
    </script>

    <?php

            } else {
                print "<span class='error'>Error: Please select two distinct airports.</span>";
            }

        } else {
            print "<span class='error'>Error: No such airport exists in this database. Please utilize the dropdown menu. Thank you.</span>";
        }

    } else {
        print "<span class='error'>Error: Please select two airports.</span>";
    }
}
?>

	<!--easyAutocomplete-->
    <script>
        var airports = {
            url: "Airports_JSON.json",
            list: {
                maxNumberOfElements: 10,
                match: {
                    enabled: true
                },showAnimation: {
            type: "slide", //normal|slide|fade
            time: 400,
            callback: function() {}
        },

        hideAnimation: {
            type: "slide", //normal|slide|fade
            time: 400,
            callback: function() {}
        }
            },
            theme: "round"
        };


        $(".airports").easyAutocomplete(airports);
    </script>

	<div id="footer">&copy; Robert Oxer 2016</div>

	<img src = "img/compass.png" alt = "compass"/>

</body>

</html>
