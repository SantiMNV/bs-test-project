<div class="border-bottom pb-2 mb-4">
  <h1 class="h2 mb-0">Users</h1>
</div>

<div class="card mb-4">
  <div class="card-header">
    <h2 class="h5 mb-0">User List</h2>
  </div>
  <div class="card-body pb-0">
    <div class="row g-2 align-items-center mb-3">
      <div class="col-sm-8 col-md-6">
        <label for="citySearch" class="form-label mb-1">Filter by city</label>
        <input
          type="search"
          id="citySearch"
          class="form-control"
          placeholder="Type a city name..."
          aria-describedby="citySearchHelp"
          autocomplete="off"
        >
      </div>
      <div class="col-sm-4 col-md-3">
        <label class="form-label d-none d-sm-block mb-1">&nbsp;</label>
        <button type="button" id="clearCitySearch" class="btn btn-outline-secondary w-100">Clear filter</button>
      </div>
    </div>
  </div>
  <div class="table-responsive">
    <table class="table table-striped table-bordered table-hover mb-0">
      <thead>
        <tr>
          <th>Name</th>
          <th>E-mail</th>
          <th>City</th>
          <th>Phone</th>
        </tr>
      </thead>
      <tbody id="userTableBody">
        <?php foreach ($users as $user) { ?>
        <tr data-city="<?= htmlspecialchars($user->getCity(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
          <td><?= htmlspecialchars($user->getName(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></td>
          <td><?= htmlspecialchars($user->getEmail(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></td>
          <td><?= htmlspecialchars($user->getCity(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></td>
          <td><?= htmlspecialchars($user->getPhone(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></td>
        </tr>
        <?php } ?>
        <tr id="cityFilterNoResults" style="display: none;">
          <td colspan="4" class="text-center text-muted">No users match this city filter.</td>
        </tr>
      </tbody>
    </table>
  </div>
</div>

<div class="card">
  <div class="card-header">
    <h2 class="h5 mb-0">Create New User</h2>
  </div>
  <div class="card-body p-4">
    <div id="createUserAjaxFeedback" class="alert d-none" role="alert" aria-live="polite"></div>

    <?php if (!empty($formErrors)) { ?>
      <div class="alert alert-danger" role="alert">
        <strong>Please fix the following errors:</strong>
        <ul class="mb-0 mt-2">
          <?php foreach ($formErrors as $error) { ?>
            <li><?= htmlspecialchars($error, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></li>
          <?php } ?>
        </ul>
      </div>
    <?php } ?>

    <form method="post" action="create.php" class="row needs-validation" novalidate id="createUserForm">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
      <input type="hidden" name="phone" id="phone_e164" value="<?= htmlspecialchars($oldInput['phone'] ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">

      <div class="row mb-2">
        <label for="name" class="col-sm-2 col-form-label">Name</label>
        <div class="col-sm-10">
          <input type="text" name="name" id="name" class="form-control" required maxlength="100" pattern="[A-Za-zÀ-ÖØ-öø-ÿ .'-]+" value="<?= htmlspecialchars($oldInput['name'] ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
          <div class="invalid-feedback">Please enter a valid name (max 100 characters).</div>
        </div>
      </div>

      <div class="row mb-2">
        <label for="email" class="col-sm-2 col-form-label">E-mail</label>
        <div class="col-sm-10">
          <input type="email" name="email" id="email" class="form-control" required maxlength="254" value="<?= htmlspecialchars($oldInput['email'] ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
          <div class="invalid-feedback">Please enter a valid e-mail address.</div>
        </div>
      </div>

      <div class="row mb-2">
        <label for="city" class="col-sm-2 col-form-label">City</label>
        <div class="col-sm-10">
          <input type="text" name="city" id="city" class="form-control" required maxlength="100" pattern="[A-Za-zÀ-ÖØ-öø-ÿ .'-]+" value="<?= htmlspecialchars($oldInput['city'] ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
          <div class="invalid-feedback">Please enter a valid city (max 100 characters).</div>
        </div>
      </div>

      <div class="row mb-2">
        <label for="phone_input" class="col-sm-2 col-form-label">Phone</label>
        <div class="col-sm-10">
          <input
            type="tel"
            name="phone_input"
            id="phone_input"
            class="form-control"
            required
            autocomplete="tel"
            value="<?= htmlspecialchars($oldInput['phone'] ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"
          >
          <div class="invalid-feedback">Please enter a valid phone number.</div>
          <small id="phoneHelp" class="text-muted">Use the country selector (flag + prefix) and enter a valid number.</small>
        </div>
      </div>

      <div class="row mb-2">
        <div class="col-sm-10 offset-sm-2">
          <button type="submit" class="btn btn-primary" id="createUserSubmit">Create new row</button>
        </div>
      </div>
    </form>
  </div>
</div>
