<div class="border-bottom pb-2 mb-4">
  <h1 class="h2 mb-0">Users</h1>
</div>

<div class="card mb-4">
  <div class="card-header">
    <h2 class="h5 mb-0">User List</h2>
  </div>
  <div class="table-responsive">
    <table class="table table-striped table-bordered table-hover mb-0">
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

<div class="card">
  <div class="card-header">
    <h2 class="h5 mb-0">Create New User</h2>
  </div>
  <div class="card-body p-4">
    <form method="post" action="create.php" class="row">
      <div class="row mb-2">
        <label for="name" class="col-sm-2 col-form-label">Name</label>
        <div class="col-sm-10">
          <input type="text" name="name" id="name" class="form-control" required>
        </div>
      </div>

      <div class="row mb-2">
        <label for="email" class="col-sm-2 col-form-label">E-mail</label>
        <div class="col-sm-10">
          <input type="email" name="email" id="email" class="form-control" required>
        </div>
      </div>

      <div class="row mb-2">
        <label for="city" class="col-sm-2 col-form-label">City</label>
        <div class="col-sm-10">
          <input type="text" name="city" id="city" class="form-control" required>
        </div>
      </div>

      <div class="row mb-2">
        <div class="col-sm-10 offset-sm-2">
          <button type="submit" class="btn btn-primary">Create new row</button>
        </div>
      </div>
    </form>
  </div>
</div>
