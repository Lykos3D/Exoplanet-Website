<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Exoplanet Website</title>
    <link rel="stylesheet" href="./css/style.css" />
    <link rel="stylesheet" href="./css/index-style.css" />
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
        <a class="current-page-link" href="./index.html"><i class="fa-solid fa-circle-info current-page-link button"></i></i>&nbsp;About</a>
        <a href="./insights.html"><i class="fa-solid fa-magnifying-glass button"></i></i>&nbsp;Insights</a>
        <a href="./query-planets.html"><i class="fa-solid fa-clipboard-list button"></i>&nbsp;Query Planets</a>
        <a href="#"><i class="fa-solid fa-chart-simple button"></i>&nbsp;Statistics</a>
  </nav>

    <section id="statistics">
        <h1>Generated Planet Map</h1>
        <?php
            /* Test: just print out whatever values the user gave in query-planets.html */
            if ($_SERVER["REQUEST_METHOD"] === "GET") {
                $min_dist = $_GET["min_dist"];
                $max_dist = $_GET["max_dist"];
                $min_year = $_GET["min_year"];
                $max_year = $_GET["max_year"];
                $min_mass = $_GET["min_mass"];
                $max_mass = $_GET["max_mass"];

                if (empty($min_dist) || empty($max_dist) || empty($min_year) || empty($max_year) || empty($min_mass) || empty($max_mass)) {
                    echo '<p>Error processing form: null value detected.</p>'
                } else {
                    /* Test to see that PHP is able to connect to HTML */
                    echo '<p>Minimum distance: ' . $min_dist . '</p><p>Maximum distance: ' . $max_dist . '</p>';
                    echo '<p>Minimum year: ' . $min_year . '</p><p>Maximum year: ' . $max_year . '</p>';
                    echo '<p>Minimum mass: ' . $min_mass . '</p><p>Maximum mass ' . $max_mass . '</p>';
                }
            } else {
                echo '<p>Please submit the form located under Query Planets. You can find the form below</p><br>'; 
                echo '<a class="button" href="./query-planets.html">Form</a>';
            }
        ?>
    </section>

 <!-- Footer -->
  <footer>
    <p>Include acknowledgements of used assets here</p>
  </footer>
</body>
</html>