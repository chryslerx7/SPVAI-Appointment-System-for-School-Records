<?php 

if(session_status() == PHP_SESSION_NONE)
{
	session_start();
}

if(isset($_SESSION['tracker'])){
?>

<!DOCTYPE html>
<html lang="">
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<link class="icon" rel="icon" type="images/x-icon" href="images/spvai.ico">

		<title>SPVAIRecordsOffice</title>

		<!-- Bootstrap CSS -->
    <link rel="stylesheet" type="text/css" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="assets/css/bootstrap-theme.min.css">

	</head>
<body style="background-color: lightblue;">

<nav class="navbar navbar-inverse">
  <div class="container-fluid">
    <div class="navbar-header">
      <a class="navbar-brand" href="index.php">SPVAIRecordsOffice</a>
    </div>
    <ul class="nav navbar-nav">
      <li class="active">
      	<a href="index.php">Rerservation
      	<span class="glyphicon glyphicon-share-alt" aria-hidden="true"></span>
      	</a>
      </li>
    </ul>
    <ul class="nav navbar-nav navbar-right">
      <li><a href="index.php"><span class="glyphicon glyphicon-backward"></span> Back To Home</a></li>
    </ul>
  </div>
</nav>


<div class="container-fluid">
	<div class="col-md-1"></div>
	<div class="col-md-10">
		<div class="panel panel-danger">
			<div class="panel-heading">
				<h3 class="panel-title">STEPS FOR BOOKING</h3>
			</div>
			<div class="panel-body">
				<div class="row">
					<div class="col-md-3">
						<div class="panel panel-default">
							<div class="panel-heading">
								<h3 class="panel-title">1. ACCOMODATION
								</h3>
							</div>
							<div class="panel-body">
								SCHEDULE OF BOOKING
							</div>
						</div>
					</div>
					<div class="col-md-3">
						<div class="panel panel-info">
							<div class="panel-heading">
								<h3 class="panel-title">2.DOCUMNET
								</h3>
							</div>
							<div class="panel-body">
								TYPES OF FILE
							</div>
						</div>
					</div>
					<div class="col-md-3">
						<div class="panel panel-success">
							<div class="panel-heading">
								<h3 class="panel-title">3. STUDENT INFO
								</h3>
							</div>
							<div class="panel-body">
								STUDENT INFO
							</div>
						</div>
					</div>
					<div class="col-md-3">
						<div class="panel panel-warning">
							<div class="panel-heading">
								<h3 class="panel-title">4. BOOKING INFO
									<span class="glyphicon glyphicon-saved" aria-hidden="true"></span>
								</h3>
							</div>
							<div class="panel-body">
								BOOKING INFO
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="col-md-1"></div>
</div>

<div class="container-fluid">
	<div class="col-md-3"></div>
	<div class="col-md-6">
		<div class="panel panel-default">
			<div class="panel-body">
			 <h2>
			 	<center>BOOKING INFO</center>
			 </h2>
			 <br />
			 <div class="panel panel-success">
			 	<div class="panel-heading">
			 		<h3 class="panel-title"><center>BOOKING INFO</center></h3>
			 	</div>
			 	<div class="panel-body">
			 		<strong>
			 			<i>Saint Peter of Verona Academy</i>
			 			<h3>
			 			<?php require_once('data/depart_from_to.php'); 
			 				echo $origin['origin_desc'];
			 			?>
			 			 - 
			 			 <?= $dest['dest_destination']; ?>
			 			 </h3>
			 			<p>Date: <?= $_SESSION['departure_date']; ?> </p>
			 		</strong>
			 			<i></i><br />
			 			<strong>
			 				<?php require_once('data/get_accomodation.php'); 
			 					echo $accomodation['acc_type'];
			 				?>
			 			</strong>
			 	</div>
			 </div>

			 <div class="panel panel-success">
			 	<div class="panel-heading">
			 		<h3 class="panel-title">CONTACT INFO</h3>
			 	</div>
			 	<div class="panel-body">
			 	<?php require_once('data/getBooked.php'); ?>
			 	<strong>Book By:</strong> <?= ucwords($bookByInfo['book_by']);  ?><br /> 
			 	<strong>Email:</strong> <?= $bookByInfo['book_contact']; ?><br />
			 	<strong>Address:</strong> <?= $bookByInfo['book_address']; ?><br />
			 	</div>
			 </div>
				<div class="container-fluid">
				<strong>
				<span class="glyphicon glyphicon-user" aria-hidden="true"></span>
			</strong>
					<table id="myTable-party" class="table table-bordered table-hover" cellspacing="0" width="100%">
							<thead>
							    <tr>
							        <th>
							        	<center>
							       			Name
							        	</center> 
							        </th>
							        <th>
							        	<center>
							        		Age
							        	</center>
						        	</th>
							        <th>
							        	<center>
							        		Gender
							        	</center>
						        	</th>
						        	 <th>
							        	<center>
							        		Price
							        	</center>
						        	</th>
							    </tr>
							</thead>
						    <tbody>
						    <?php
						    	require_once('data/getBooked.php');
						    	$totalPayment = 0;
						    	$tracker = '';
						     foreach($bookPass as $bp): 
						    	$name = $bp['book_name'];
						    	$name = ucwords($name);
						    	$price = $bp['acc_price'];
						    	$totalPayment += $price;
						    	$tracker = $bp['book_tracker'];
						    ?>
						    	<tr align="center">
						    		<td><?= $name; ?></td>
						    		<td><?= $bp['book_age']; ?></td>
						    		<td><?= $bp['book_gender']; ?></td>
						    		<td><?= $price; ?></td>
						    	</tr>
						    <?php endforeach; ?>
						    	<tr>
						    		<td></td>
						    		<td></td>
						    	</tr>
						    </tbody>
						    <strong>- Booked ID: <?= $tracker; ?></strong>
						   </table>
						   <center>
							   <a href="index.php" class="btn btn-success">Return Home
								   <span class="glyphicon glyphicon-backward" aria-hidden="true"></span>
							   </a>
						   </center>
				</div>
			</div>
		</div>
	</div>
	<div class="col-md-3"></div>
</div>

<script type="text/javascript" src="assets/js/jquery-3.1.1.min.js"></script>
<script type="text/javascript" src="assets/js/bootstrap.min.js"></script>



</body>
</html>

<?php
}else{
	echo '<strong>';
	echo 'Page Not Exist';
	echo '</strong>';
}//end if else isset

 ?>