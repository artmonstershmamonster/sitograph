<form class="form-signin" id="user_login_form" method="POST" action="{$lang_url}/login/">
  <img class="mb-4" src="/docs/4.3/assets/brand/bootstrap-solid.svg" alt="" width="72" height="72">
  <h1 class="h3 mb-3 font-weight-normal">Личный кабинет</h1>
  <label for="inputEmail" class="sr-only">Email address</label>
  <input name="email" type="email" id="inputEmail" class="form-control" placeholder="Email address" required autofocus>
  <label for="inputPassword" class="sr-only">Password</label>
  <input name="password" type="password" id="inputPassword" class="form-control" placeholder="Password" required>
  <button type="submit" class="btn btn-lg btn-primary btn-block" name="doLogin" value="1">{_t("users.form.sign_in")}</button>
</form>


<form class="form-horizontal" id="user_login_form" method="POST" action="{$lang_url}/login/" hidden>
  <div class="form-group">
    <label for="inputEmail" class="col-sm-2 control-label">{_t("users.form.email")}</label>
    <div class="col-sm-10">
      <input type="text" class="form-control" id="inputEmail" placeholder="{_t("users.form.email")}" name="email">
    </div>
  </div>
  <div class="form-group">
    <label for="inputPassword" class="col-sm-2 control-label">{_t("users.form.password")}</label>
    <div class="col-sm-10">
      <input type="password" class="form-control" id="inputPassword" placeholder="{_t("users.form.password")}" name="password">
    </div>
  </div>
  <div class="form-group">
    <div class="col-sm-offset-2 col-sm-6">
      <div class="checkbox">
        <label>
          <input type="checkbox" checked> {_t("users.form.remember_me")}
        </label>
      </div>
    </div>
    <div class="col-sm-4 text-right">
      <a href="{$lang_url}/password-reset/">{_t("users.form.link_forget")}</a>
    </div>
  </div>
  <div class="form-group">
    <div class="col-sm-offset-2 col-sm-4">
      <button type="submit" class="btn btn-lg btn-primary" name="doLogin" value="1">{_t("users.form.sign_in")}</button>
    </div>
    <div class="col-sm-offset-1 col-sm-5">
      <a href="{$google_user_auth_url}"><img src="{$contentUrl}/images/btn_google_signin.png" title="{_t("users.form.sign_in_with_google")}" class="img-responsive"></a>
    </div>
  </div>
</form>