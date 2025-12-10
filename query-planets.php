<?php
    // This essentially controls whether the "Generate Planet Map" button appears or not
    $form_submitted = false;

    // These are the connection details for the database
    $host = 'localhost';
    $user = 'root';
    $password = '';
    $database = 'exoplanet_database';

    // Connection to the database
    $conn = new mysqli($host, $user, $password, $database);
    if ($conn->connect_error) {
        die("Unable to connect to database: " . $conn->connect_error);
    }

    // This checks whether a form was submitted
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // Mark that the form was submitted
        $form_submitted = true;

        // THIS IS THE START OF THE TOTAL PLANETS IN DATABASE CALCULATION
        $total_planets_query = "SELECT COUNT(*) AS total FROM Planets";
        $total_planets_result = mysqli_query($conn, $total_planets_query);
        $total_planets_row = mysqli_fetch_assoc($total_planets_result);
        $total_planets = $total_planets_row["total"];

        // THIS IS THE START OF THE YEAR RANGE PERCENTAGE CALCULATION
        $min_year = isset($_POST["min_year"]) ? $_POST["min_year"] : 1992;
        $max_year = isset($_POST["max_year"]) ? $_POST["max_year"] : 2025;

        // Swap min_year and max_year if they are reversed
        if ($min_year > $max_year) {
            $temp = $min_year;
            $min_year = $max_year;
            $max_year = $temp;
        }

        // Calculate the number of planets in that year range
        $planets_in_year_range_query = "SELECT COUNT(*) AS total_in_range FROM Planets p JOIN Discovery_Details d ON p.planet_id = d.planet_id WHERE d.discovery_year BETWEEN $min_year AND $max_year";
        $planets_in_year_range_result = mysqli_query($conn, $planets_in_year_range_query);
        $planets_in_year_range_row = mysqli_fetch_assoc($planets_in_year_range_result);
        $planets_in_year_range = $planets_in_year_range_row["total_in_range"];

        // Calculate percentage of planets in year range (also make sure total_planets is not 0 to avoid divide by zero)
        $planets_in_year_percentage = $total_planets > 0 ? ($planets_in_year_range / $total_planets) * 100 : 0;

        // THIS IS THE START OF THE PLANET DISTANCE RANGE PERCENTAGE CALCULATION
        $min_dist = isset($_POST["min_dist"]) ? $_POST["min_dist"] : 0;
        $max_dist = isset($_POST["max_dist"]) ? $_POST["max_dist"] : 10000;

        // Again, swap min_dist and max_dist if they are swapped
        if ($min_dist > $max_dist) {
            $temp = $min_dist;
            $min_dist = $max_dist;
            $max_dist = $temp;
        }

        // Calculate the number of planets in that distance range
        $planets_in_distance_range_query = "SELECT COUNT(*) AS total_in_range FROM Planets p JOIN Stars s ON p.star_id = s.star_id WHERE s.star_distance BETWEEN $min_dist AND $max_dist";
        $planets_in_distance_range_result = mysqli_query($conn, $planets_in_distance_range_query);
        $planets_in_distance_range_row = mysqli_fetch_assoc($planets_in_distance_range_result);
        $planets_in_distance_range = $planets_in_distance_range_row["total_in_range"];

        // Calculate percentage of planets in distance range (again make sure that total_planets is not 0)
        $planets_in_distance_percentage = $total_planets > 0 ? ($planets_in_distance_range / $total_planets) * 100 : 0;

        // THIS IS THE START OF THE PLANET ORBITAL DISTANCE PERCENTAGE CALCULATION
        $min_orbit = isset($_POST["min_orbit"]) ? $_POST["min_orbit"] : 0;
        $max_orbit = isset($_POST["max_orbit"]) ? $_POST["max_orbit"] : 100;

        // Again swap min_orbit and max_orbit if they are swapped
        if ($min_orbit > $max_orbit) {
            $temp = $min_orbit;
            $min_orbit = $max_orbit;
            $max_orbit = $temp;
        }

        // Calculate the number of planets in the orbital distance range
        $planets_in_orbit_range_query = "SELECT COUNT(*) AS total_in_range FROM Planets p WHERE p.orbital_distance BETWEEN $min_orbit AND $max_orbit";
        $planets_in_orbit_range_result = mysqli_query($conn, $planets_in_orbit_range_query);
        $planets_in_orbit_range_row = mysqli_fetch_assoc($planets_in_orbit_range_result);
        $planets_in_orbit_range = $planets_in_orbit_range_row["total_in_range"];

        // Calculate percentage of planets in orbit range (again checking total_planets)
        $planets_in_orbit_range_percentage = $total_planets > 0 ? ($planets_in_orbit_range / $total_planets) * 100 : 0;

        // THIS IS THE START OF THE DISCOVERY METHOD PERCENTAGE CALCULATION
        $methods = isset($_POST["discovery_method"]) ? $_POST["discovery_method"]  : [];

        // Perform a database query if at least on method was checked
        if (!empty($methods)) {
            // This creates a comma separated list needed for the query
            $methods_list = '';
            for ($method = 0; $method < count($methods); $method++) {
                if ($method === count($methods) - 1) {
                    $methods_list .= "'" . $methods[$method] . "'";
                } else {
                    $methods_list .= "'" . $methods[$method] . "'". ",";
                }
            }
            $planets_discovered_in_list_query = "SELECT COUNT(*) AS total FROM Planets p JOIN Discovery_Details d ON p.planet_id = d.planet_id JOIN Discovery_methods m ON d.method_id = m.method_id WHERE m.name IN ($methods_list)";
            $planets_discovered_in_list_result = mysqli_query($conn, $planets_discovered_in_list_query);
            $planets_discovered_in_list_row = mysqli_fetch_assoc($planets_discovered_in_list_result);
            $planets_discovered_in_list = $planets_discovered_in_list_row["total"];
        } else {
            // If no boxes were selected, then the result must be 0
            $planets_discovered_in_list = 0;
        }

        // Calculate percentage of planets which were discovered with the selected methods
        $planets_discovered_in_list_percentage = $total_planets > 0 ? ($planets_discovered_in_list / $total_planets * 100) : 0;
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exoplanet Website</title>
    <link rel="stylesheet" href="./css/style.css">
    <link rel="stylesheet" href="./css/query-style.css">
    <link rel="icon" href="images/planet_tab_image.png" type="image/png">
    <!-- This is needed for the Rubik font to work, from Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Rubik:wght@400;500;700&display=swap" rel="stylesheet">
    <!-- This is needed for icons, hosted by Font Awesome -->
    <script src="https://kit.fontawesome.com/68f1cdffaa.js" crossorigin="anonymous"></script>
</head>
<body>
    <!-- Navigation bar -->
    <nav>
        <div class="logo"><img id="nav-img" src="./images/planet_sidebar_image.png" alt="Planet image"><h3>Exoplanet<br>Search!</h3></div>
        <a href="./index.html"><i class="fa-solid fa-circle-info button"></i></i>&nbsp;About</a>
        <a href="./insights.html"><i class="fa-solid fa-magnifying-glass button"></i></i>&nbsp;Insights</a>
        <a class="current-page-link" href="query-planets.php"><i class="fa-solid fa-clipboard-list button current-page-link"></i>&nbsp;Query Planets</a>
        <a href="./statistics.php"><i class="fa-solid fa-chart-simple button"></i>&nbsp;Statistics</a>
    </nav>

    <!-- Query Planets Section -->
    <section id="query-planets">
        <h1>Query Planets</h1>
        <p>This page will allow you to query the Exoplanet database to generate personalized statistics and a planet map!</p>
    </section>

    <!-- Select Planets Section -->
    <section id="select-planets">
        <h1>Select Planets</h1>
        <form action="query-planets.php" method="POST">
            <div class="card-grid">
                <!-- Distance from Earth-->

                <!-- Minimum distance -->
                <div class="card">
                    <h1 class="card-header">Minimum Distance from Earth</h1>
                    <label for="min-distance-from-earth"><span id="min_distance"></span><span>&nbsp;Parsecs</span></label>
                    <input type="range" id="min-distance-from-earth" name="min_dist" min="0" max="10000" value="0">
            
                    <!-- Display slider value -->
                    <script>
                        let min_dist_slider = document.getElementById("min-distance-from-earth");
                        let min_dist_output = document.getElementById("min_distance");

                        min_distance.textContent = min_dist_slider.value;

                        min_dist_slider.addEventListener('input', function() {
                            min_dist_output.textContent = this.value;
                        });
                    </script>

                    <!-- Maximum Distance -->
                        <h1 class="card-header">Maximum Distance from Earth</h1>
                        <label for="max-distance-from-earth"><span id="max_distance"></span><span>&nbsp;Parsecs</span></label>
                        <input type="range" id="max-distance-from-earth" name="max_dist" min="0" max="10000" value="10000">

                        <!-- Display slider value -->
                        <script>
                            let max_dist_slider = document.getElementById("max-distance-from-earth");
                            let max_dist_output = document.getElementById("max_distance");

                            max_distance.textContent = max_dist_slider.value;

                            max_dist_slider.addEventListener('input', function() {
                                max_dist_output.textContent = this.value;
                            });
                        </script>
                </div>

                <!-- Year of Discovery -->

                <!-- Minimum Year -->
                <div class="card">
                    <h1 class="card-header">Earliest Discovery Year</h1>
                    <label for="min-discovery-year"><span>Year:&nbsp;</span><span id="min_year"></span></label>
                    <input type="range" id="min-discovery-year" name="min_year" min="1992" max="2025" value="1992">
                
                    <!-- Display slider value -->
                    <script>
                        let min_year_slider = document.getElementById("min-discovery-year");
                        let min_year_output = document.getElementById("min_year");

                        min_year.textContent = min_year_slider.value;

                        min_year_slider.addEventListener('input', function() {
                            min_year_output.textContent = this.value;
                        });
                    </script>

                    <!-- Maximum Year -->
                    <h1 class="card-header">Latest Discovery Year</h1>
                    <label for="max-discovery-year"><span>Year:&nbsp;</span><span id="max_year"></span></label>
                    <input type="range" id="max-discovery-year" name="max_year" min="1992" max="2025" value="2025">

                    <!-- Display slider value -->
                    <script>
                        let max_year_slider = document.getElementById("max-discovery-year");
                        let max_year_output = document.getElementById("max_year");

                        max_year.textContent = max_year_slider.value;

                        max_year_slider.addEventListener('input', function() {
                            max_year_output.textContent = this.value;
                        });
                    </script>
                </div>

                <!-- Minimum Orbital Radius -->
                <div class="card">
                    <h1 class="card-header">Minimum Orbital Radius</h1>
                    <label for="min-orbit"><span id="min_orbit"></span><span>&nbsp;AUs</span></label>
                    <input type="range" id="min-orbit" name="min_orbit" min="0" max="100" value="0">
            
                    <!-- Display slider value -->
                    <script>
                        let min_orbit_slider = document.getElementById("min-orbit");
                        let min_orbit_output = document.getElementById("min_orbit");

                        min_orbit.textContent = min_orbit_slider.value;

                        min_orbit_slider.addEventListener('input', function() {
                            min_orbit_output.textContent = this.value;
                        });
                    </script>

                    <!-- Maximum Orbital Radius -->
                    <h1 class="card-header">Maximum Orbital Radius</h1>
                    <label for="max-orbit"><span id="max_orbit"></span><span>&nbsp;AUs</span></label>
                    <input type="range" id="max-orbit" name="max_orbit" min="0" max="100" value="100">

                    <!-- Display slider value -->
                    <script>
                        let max_orbit_slider = document.getElementById("max-orbit");
                        let max_orbit_output = document.getElementById("max_orbit");

                        max_orbit.textContent = max_orbit_slider.value;

                        max_orbit_slider.addEventListener('input', function() {
                            max_orbit_output.textContent = this.value;
                        });
                    </script>
                </div>

                <!-- Discovery Method -->
                <div class="card">
                    <h1 class="card-header">Discovery Method</h1>
                    <div class="checkbox-container">
                        <div>
                            <input type="checkbox" id="transit" name="discovery_method[]" value="Transit">
                            <label for="transit">&nbsp;Transit</label>
                        </div>
                        <div>
                            <input type="checkbox" id="radial-velocity" name="discovery_method[]" value="Radial Velocity">
                            <label for="radial-velocity">&nbsp;Radial Velocity</label>
                        </div>
                        <div>
                            <input type="checkbox" id="microlensing" name="discovery_method[]" value="Microlensing">
                            <label for="microlensing">&nbsp;Microlensing</label>
                        </div>
                        <div>
                            <input type="checkbox" id="imaging" name="discovery_method[]" value="Imaging">
                            <label for="imaging">&nbsp;Imaging</label>
                        </div>
                    </div>
                </div>
            </div>
            <input class="button" type="submit" value="Submit">
        </form>
    </section>
    <section id="results">
        <?php
        // This displays the percentage of planets discovered within the specified year range
        echo "<div class='card-grid'>";

        // This displays the percentage of planets discovered within the specified distance range
        if(isset($planets_in_distance_percentage)) {
            echo "<div class='card'>";
            echo "<p>Percentage of planets within distance range of $min_dist Parsecs to $max_dist Parsecs from Earth:</p>";
            echo "<b>" . number_format($planets_in_distance_percentage, 2) . "%</b>";
            echo "</div>";
        }

        // This displays the percentage of planets discovered within the specified time frame
        if (isset($planets_in_year_percentage)) {
            echo "<div class='card'>";
            echo "<p>Percentage of planets discovered between $min_year and $max_year:</p>";
            echo "<b>" . number_format($planets_in_year_percentage, 2) . "%</b>";
            echo "</div>";
        }

        // This displays the percentage of planets discovered within the specified orbital distance range
        if(isset($planets_in_orbit_range_percentage)) {
            echo "<div class='card'>";
            echo "<p>Percentage of planets within orbital range of $min_orbit AUs to $max_orbit AUs from their host star:</p>";
            echo "<b>" . number_format($planets_in_orbit_range_percentage, 2) . "%</b>";
            echo "</div>";
        }

        // This displays the percentage of planets discovered using the indicated discovery methods
        if(isset($planets_discovered_in_list)) {
            echo "<div class='card'>";
            echo "<p>Percentage of planets discovered using one of the following methods:</p>";
            echo "<ul>";
            for ($i = 0; $i < count($methods); $i++) {
                echo "<li>" . $methods[$i] . "</li>";
            }
            echo "</ul>";
            echo "<b>" . number_format($planets_discovered_in_list_percentage, 2) . "%</b>";
            echo "</div>";
        }
        echo "</div>";
        ?>

        <!-- This will display the Generate Planet Map button, which will generate the planet map in statistics.php -->
        <?php if ($form_submitted): ?>
            <form action="statistics.php" method="POST">
                <input type="hidden" name="min_year" value="<?php echo $min_year; ?>">
                <input type="hidden" name="max_year" value="<?php echo $max_year; ?>">
                <input type="hidden" name="min_dist" value="<?php echo $min_dist; ?>">
                <input type="hidden" name="max_dist" value="<?php echo $max_dist; ?>">
                <input type="hidden" name="min_orbit" value="<?php echo $min_orbit; ?>">
                <input type="hidden" name="max_orbit" value="<?php echo $max_orbit; ?>">

                <?php foreach ($methods as $method): ?>
                    <input type="hidden" name="discovery_method[]" value="<?php echo $method; ?>">
                <?php endforeach; ?>

                <input style="margin-top: 50px" class="button" type="submit" value="Create Map">
            </form>
        <?php endif; ?>
    </section>

    <!-- Footer -->
    <footer>
        <p>Tab planet image attribution: <a href="https://www.flaticon.com/free-icons/planet">Planet icons created by monkik - Flaticon</a>;&nbsp</p><p>Sidebar planet image attribution: <a href="https://www.flaticon.com/free-icons/planet">Planet icons created by Nsit - Flaticon</a></p>
    </footer>
</body>
</html>