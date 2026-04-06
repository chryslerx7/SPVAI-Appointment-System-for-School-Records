<?php 

if(session_status() == PHP_SESSION_NONE)
{
	session_start();
}

if(isset($_SESSION['departure_date'])){
?>

<!DOCTYPE html>
<html lang="">
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<link rel="icon" type="image/x-icon" href="images/spvai.ico">

		<title>SPVAIRecordsOffice</title>

		
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
      	<a href="index.php">Reservation
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
								SCHEDULE OPF BOOKING
							</div>
						</div>
					</div>
					<div class="col-md-3">
						<div class="panel panel-info">
							<div class="panel-heading">
								<h3 class="panel-title">2. DOCUMENT
								</h3>
							</div>
							<div class="panel-body">
								TYPE OF FILE
							</div>
						</div>
					</div>
					<div class="col-md-3">
						<div class="panel panel-success">
							<div class="panel-heading">
								<h3 class="panel-title">3. STUDENT INFO</h3>
							</div>
							<div class="panel-body">
								STUDENT DETAILS
							</div>
						</div>
					</div>
					<div class="col-md-3">
						<div class="panel panel-warning">
							<div class="panel-heading">
								<h3 class="panel-title">4. BOOKING INFO</h3>
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
			 	<center> Records Office </center>
			 </h2>
				<div class="container-fluid">
					<form class="form-horizontal" role="form" id="form-acc">
					  <table id="myTable-party" class="table table-bordered table-hover" cellspacing="0" width="100%">
							<thead>
							    <tr>
							        <th> <span class="glyphicon glyphicon-record" aria-hidden="true"></span> 
							        Type of File
							        </th>
							        <th>
							        	<center>
							        		Copy
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
						   		<?php require_once('data/get_all_accomodations.php'); ?>
						   		<tr>
						   			<td>
						   				<input value="<?= $getSit['acc_id']; ?>" type="radio" name="acc">
										<label>SF10</label>
										
						   				
						   			</td>
						   			<td align="center">1 per head
						   			</td>
						   			<td align="center">100</td>
						   		</tr>
						   		<tr>
						   			<td>
						   				<input value="<?= $getEcoA['acc_id']; ?>" type="radio" name="acc">
						   				<label>CERTIFICATION OF ENROLLMENT</label>
										   
						   			</td>
						   			<td align="center">1 per head
						   				
						   			</td>
						   			<td align="center">100</td>
									   
						   		</tr>
						   		<tr>
						   			<td>
						   				<input value="<?= $getEcoB['acc_id']; ?>" type="radio" name="acc">
						   				<label>CERTIFICATION OF GRADUATION</label>
										   
						   			</td>
						   			<td align="center">1 per head
						   				
						   			</td>
						   			<td align="center">100</td>
						   		</tr>
						   		<tr>
						   			<td>
						   				<input value="<?= $getTour['acc_id']; ?>" type="radio" name="acc">
						   				<label>ENGLISH MEDIUM OF INSTRUCTIONS</label>
										   
						   			</td>
						   			<td align="center">1 per head
						   				
						   			</td>
						   			<td align="center">100</td>
						   		</tr>
						   		
						    </tbody>
					    </table>
				      <div class="form-group">
					    <label for="">No. of Copies</label>
					    <input type="number" min="0" class="form-control" name="totalPass" plactreholder="Total # of Student" autocomplete="off">
					  </div>
					  <button type="submit" class="btn btn-success">
					  <span class="glyphicon glyphicon-arrow-right" aria-hidden="true"></span>
					  </button>
					</form>
				</div>
			</div>
		</div>
	</div>
	<div class="col-md-3"></div>
</div>

<script type="text/javascript" src="assets/js/jquery-3.1.1.min.js"></script>
<script type="text/javascript" src="assets/js/bootstrap.min.js"></script>


<script type="text/javascript">
	$(document).on('submit', '#form-acc', function(event) {
		event.preventDefault();
		/* Act on the event */
		var acc = $('input[name="acc"]:checked').val();
		var totalPass = $('input[name="totalPass"]').val();

		if(acc == null){
			alert('Please Select Type of document !');
		}else{
			//console.log(acc);
			if(totalPass.length == 0){
				alert('Please Enter Number of Copies!');
			}else{
				$.ajax({
						url: 'data/session_accomodation.php',
						type: 'post',
						dataType: 'json',
						data: {
							acc : acc,
							tp : totalPass
						},
						success: function (data) {
							console.log(data.slot);
							window.location = "passenger.php";
							if(data.slot >= 0){
								window.location = "passenger.php";
							}else{
								alert('One Copy per Head!');
							}
						},
						error: function(){
							alert('Error: L175+');
						}
					});
			}//end totalPass
		}//end acc == null
	});

</script>

</body>
</html>


<?php
}else{
	echo '<strong>';
	echo 'Page Not Exist';
	echo '</strong>';
}//end if else isset

 ?>