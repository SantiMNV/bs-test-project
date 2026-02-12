<div class="page-header">
  <h1>Users</h1>
</div>

<div class="panel panel-default">
  <div class="panel-heading">
    <h3 class="panel-title">User List</h3>
  </div>
  <div class="table-responsive">
    <table class="table table-striped table-bordered table-hover">
      <thead>
        <tr>
          <th>Name</th>
          <th>E-mail</th>
          <th>City</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($users as $user) { ?>
        <tr>
          <td><?= $user->getName() ?></td>
          <td><?= $user->getEmail() ?></td>
          <td><?= $user->getCity() ?></td>
        </tr>
        <?php } ?>
      </tbody>
    </table>
  </div>
</div>

<div class="panel panel-default">
  <div class="panel-heading">
    <h3 class="panel-title">Create New User</h3>
  </div>
  <div class="panel-body">
    <form method="post" action="create.php" class="form-horizontal">
      <div class="form-group">
        <label for="name" class="col-sm-2 control-label">Name</label>
        <div class="col-sm-10">
          <input type="text" name="name" id="name" class="form-control" required>
        </div>
      </div>

      <div class="form-group">
        <label for="email" class="col-sm-2 control-label">E-mail</label>
        <div class="col-sm-10">
          <input type="email" name="email" id="email" class="form-control" required>
        </div>
      </div>

      <div class="form-group">
        <label for="city" class="col-sm-2 control-label">City</label>
        <div class="col-sm-10">
          <input type="text" name="city" id="city" class="form-control" required>
        </div>
      </div>

      <div class="form-group">
        <div class="col-sm-offset-2 col-sm-10">
          <button type="submit" class="btn btn-primary">Create new row</button>
        </div>
      </div>
    </form>
  </div>
</div>
