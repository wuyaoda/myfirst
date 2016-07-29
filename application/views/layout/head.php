<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo $this->title;?></title>
    <link href="<?php echo $this->basedir?>/public/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?php echo $this->basedir?>/public/css/datepicker.css" rel="stylesheet">
    <script src="<?php echo $this->basedir?>/public/js/jquery.min.js"></script>
    <script src="<?php echo $this->basedir?>/public/js/bootstrap.min.js"></script>
    <script src="<?php echo $this->basedir?>/public/js/bootstrap-datepicker.js"></script>
    <script src="<?php echo $this->basedir?>/public/js/echarts.min.js"></script>
</head>
<body>
<?php 
//customer not implement
if(!empty($_GET["start"]) && !empty($_GET["end"])){
  $start = Redis::getInstance()->set("start",$_GET["start"]);
  $end = Redis::getInstance()->set("end",$_GET["end"]);
}else{
  $start = Redis::getInstance()->get("start",date('Y-m-d'));
  $end = Redis::getInstance()->get("end",date('Y-m-d'));  
}
?>
<nav class="navbar navbar-default">
    <div class="container">
      <div class="navbar-header">
        <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target=".navbar-collapse">
          <span class="sr-only">Toggle navigation</span>
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>
        </button>
        <a class="navbar-brand" href="#">Project name</a>
      </div>
      <div class="navbar-collapse collapse">
        <ul class="nav navbar-nav">
          <li class="active"><a href="#">Home</a></li>
          <li><a href="#about">About</a></li>
          <li><a href="#contact">Contact</a></li>
          <li class="dropdown">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Dropdown <span class="caret"></span></a>
            <ul class="dropdown-menu">
              <li><a href="#">Action</a></li>
              <li><a href="#">Another action</a></li>
              <li><a href="#">Something else here</a></li>
              <li role="separator" class="divider"></li>
              <li class="dropdown-header">Nav header</li>
              <li><a href="#">Separated link</a></li>
              <li><a href="#">One more separated link</a></li>
            </ul>
          </li>
        </ul>
      </div><!--/.nav-collapse -->
    </div>
</nav>
<div class="wrap">
    <div class="container">
<form class="form-inline" style="float:right"  action="" method="get">
  <div class="form-group">
    <label class="sr-only" for="from_date">Start Date</label>
    <input type="text" name="start" class="datepicker-control form-control" value="<?php echo $start;?>" id="start_date" data-date-format="mm/dd/yyyy" placeholder="Start Date">
  </div>
  <div class="form-group">
    <label class="sr-only" for="end_date">End Date</label>
    <input type="text" name="end" class="datepicker-control form-control" value="<?php echo $end;?>" id="end_date" data-date-format="mm/dd/yyyy" placeholder="End Date">
  </div>
  <button type="submit" class="btn btn-default">Submit</button>
</form>
<div style="clear:both"></div>
</div></div>
<script>
    $(function(){
        $('.datepicker-control').datepicker({
            format: 'yyyy-mm-dd',
            startDate: '-3d'
        });
        $(".datepicker-control").focus(function(){
            var index = $(this).index(".datepicker-control");
            $(".datepicker").hide();
            $(".datepicker").eq(index).show();
        });
        $('#end_date').datepicker().on('changeDate', function(ev){
            if ($('#end_date').val() < $('#start_date').val()){
                $('#end_date').val($('#start_date').val());
            }
        });
       $('#start_date').datepicker().on('changeDate', function(ev){
            if ($('#end_date').val() < $('#start_date').val()){
                $('#start_date').val($('#end_date').val());
            }
        });
    });
</script>