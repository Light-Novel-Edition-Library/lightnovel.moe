<?php 
if(isset($_GET['1']) && $_GET['1']!=""){
	$_1 = $_GET['1'];
}else{
	$_1 = "";
}

require("var/header.php")
?>

		
		<div class="container">
			<?php 
			
			if($_1 == "login"){
				require("var/login.php");
			}elseif($_1 == "profile"){
				require("var/profile.php");
			}elseif($_1 == "privacy"){
				require("var/privacy.php");
			}elseif($_1 == "links"){
				require("var/links.php");
			}
			
			require("var/footer.php")
			?>
		  
		</div>
	</body>
</html>
