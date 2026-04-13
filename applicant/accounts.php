<?php 
  $applicant = new Applicants();
  $appl = $applicant->single_applicant($_SESSION['APPLICANTID']);
?>
<style>
  .form-control {
    border-radius: 4px;
  }
  .panel {
    border-radius: 6px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
  }
  .panel-heading {
    background-color: #337ab7;
    color: white;
    font-size: 18px;
    font-weight: bold;
    padding: 15px;
    border-top-left-radius: 6px;
    border-top-right-radius: 6px;
  }
  .btn-primary {
    border-radius: 4px;
  }
</style>

<div class="container-fluid">
  <div class="panel panel-default">
    <div class="panel-heading">
      Update Your Profile
    </div>
    <div class="panel-body">
      <form class="form-horizontal" method="POST" action="controller.php?action=edit">

        <div class="form-group">
          <label class="col-sm-3 control-label" for="FNAME">First Name</label>
          <div class="col-sm-9">
            <input type="text" class="form-control" id="FNAME" name="FNAME" value="<?php echo $appl->FNAME; ?>" onkeyup="capitalize(this.id, this.value);" autocomplete="off">
          </div>
        </div>

        <div class="form-group">
          <label class="col-sm-3 control-label" for="LNAME">Last Name</label>
          <div class="col-sm-9">
            <input type="text" class="form-control" id="LNAME" name="LNAME" value="<?php echo $appl->LNAME; ?>" onkeyup="capitalize(this.id, this.value);" autocomplete="off">
          </div>
        </div>

        <div class="form-group">
          <label class="col-sm-3 control-label" for="MNAME">Middle Name</label>
          <div class="col-sm-9">
            <input type="text" class="form-control" id="MNAME" name="MNAME" value="<?php echo $appl->MNAME; ?>" onkeyup="capitalize(this.id, this.value);" autocomplete="off">
          </div>
        </div>

        <div class="form-group">
          <label class="col-sm-3 control-label" for="ADDRESS">Address</label>
          <div class="col-sm-9">
            <textarea class="form-control" id="ADDRESS" name="ADDRESS" rows="2" onkeyup="capitalize(this.id, this.value);" autocomplete="off"><?php echo $appl->ADDRESS; ?></textarea>
          </div>
        </div>

        <div class="form-group">
          <label class="col-sm-3 control-label">Gender</label>
          <div class="col-sm-9">
            <label class="radio-inline">
              <input type="radio" name="optionsRadios" value="Female" <?php if($appl->SEX == "Female") echo "checked"; ?>> Female
            </label>
            <label class="radio-inline">
              <input type="radio" name="optionsRadios" value="Male" <?php if($appl->SEX == "Male") echo "checked"; ?>> Male
            </label>
          </div>
        </div>

        <div class="form-group">
          <label class="col-sm-3 control-label" for="BIRTHDATE">Date of Birth</label>
          <div class="col-sm-9">
            <div class="input-group">
              <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
              <input type="text" class="form-control date_picker" id="BIRTHDATE" name="BIRTHDATE" value="<?php echo date_format(date_create($appl->BIRTHDATE), 'm/d/Y'); ?>" autocomplete="off">
            </div>
          </div>
        </div>

        <div class="form-group">
          <label class="col-sm-3 control-label" for="BIRTHPLACE">Place of Birth</label>
          <div class="col-sm-9">
            <textarea class="form-control" id="BIRTHPLACE" name="BIRTHPLACE" rows="2" onkeyup="capitalize(this.id, this.value);" autocomplete="off"><?php echo $appl->BIRTHPLACE; ?></textarea>
          </div>
        </div>

        <div class="form-group">
          <label class="col-sm-3 control-label" for="TELNO">Contact Number</label>
          <div class="col-sm-9">
            <input type="text" class="form-control" id="TELNO" name="TELNO" value="<?php echo $appl->CONTACTNO; ?>" autocomplete="off">
          </div>
        </div>

        <div class="form-group">
          <label class="col-sm-3 control-label" for="CIVILSTATUS">Civil Status</label>
          <div class="col-sm-9">
            <select class="form-control" id="CIVILSTATUS" name="CIVILSTATUS">
              <option value="none">Select</option>
              <option value="Single" <?php if($appl->CIVILSTATUS == "Single") echo "selected"; ?>>Single</option>
              <option value="Married" <?php if($appl->CIVILSTATUS == "Married") echo "selected"; ?>>Married</option>
              <option value="Widow" <?php if($appl->CIVILSTATUS == "Widow") echo "selected"; ?>>Widow</option>
            </select>
          </div>
        </div>

        <div class="form-group">
          <label class="col-sm-3 control-label" for="EMAILADDRESS">Email Address</label>
          <div class="col-sm-9">
            <input type="email" class="form-control" id="EMAILADDRESS" name="EMAILADDRESS" value="<?php echo $appl->EMAILADDRESS; ?>" autocomplete="off">
          </div>
        </div>

        <div class="form-group">
          <label class="col-sm-3 control-label" for="DEGREE">Educational Attainment</label>
          <div class="col-sm-9">
            <input type="text" class="form-control" id="DEGREE" name="DEGREE" value="<?php echo $appl->DEGREE; ?>" onkeyup="capitalize(this.id, this.value);" autocomplete="off">
          </div>
        </div>

        <div class="form-group text-center">
          <div class="col-sm-offset-3 col-sm-9">
            <button type="submit" class="btn btn-primary">
              <i class="fa fa-save"></i> Save Changes
            </button>
          </div>
        </div>

      </form>
    </div>
  </div>
</div>
