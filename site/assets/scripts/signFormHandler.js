$(document).ready(() => {
  const createConfigObject = (
    signIn,
    signUp,
    switcher,
    userNameField,
    userCompanyNameField,
    userEmailField,
    passwordField,
    rememberMeField,
    submitFormButton
  ) => ({
    signIn,
    signUp,
    switcher,
    userNameField,
    userCompanyNameField,
    userEmailField,
    passwordField,
    rememberMeField,
    submitFormButton,
  });
  const languageConfig = {
    rus: createConfigObject(
      "Логин",
      "Регистрация",
      "рус",
      "Имя пользователя",
      "Название компании",
      "Ваша почта",
      "Пароль",
      "Запомнить меня",
      "Принять"
    ),
    eng: createConfigObject(
      "Sign in",
      "Sign up",
      "eng",
      "Username",
      "Company name",
      "Your mail",
      "Password",
      "Remember me",
      "Accept"
    ),
  };
  const updateLanguage = (lang) => {
    $("#signInButton").text(languageConfig[lang].signIn);
    $("#signUpButton").text(languageConfig[lang].signUp);
    $(".switch__label").text(languageConfig[lang].switcher);
    if (isSignIn) {
      $("#signInButton").addClass("selected");
      $("#loginID").attr("placeholder", languageConfig[lang].userNameField);
      $("#loginPassword").attr("placeholder", languageConfig[lang].passwordField);
      $("#signFormLabel").text(languageConfig[lang].rememberMeField);
    } else {
      $("#new_user").attr("placeholder", languageConfig[lang].userNameField);
      $("#new_email").attr("placeholder", languageConfig[lang].userEmailField);
      $("#new_company").attr(
        "placeholder",
        languageConfig[lang].userCompanyNameField
      );
    }
    $("#formButton").text(languageConfig[lang].submitFormButton);
  };
  const createForm = (currButton, prevButton) => {
    isSignIn = !isSignIn;
    $(currButton).addClass("selected");
    $(prevButton).removeClass("selected");
    $(".sign__form-body").empty();
  };
  let language = "rus";
  let isSignIn = true;

  updateLanguage(language);

  $("#signInButton").click(function () {
    if (!isSignIn) {
      createForm(this, "#signUpButton");
      $(".sign__form-body").append(
        '<div class="sign__form-input"><input type="text" id="loginID" /><input type="text" id="loginPassword" /></div> <div class="sign__form-checkbox"><input type="checkbox" id="rememberMe" /><label for="rememberMe" id="signFormLabel"></label></div>'
      );
      updateLanguage(language);
    }
  });
  $("#signUpButton").click(function () {
    if (isSignIn) {
      createForm(this, "#signInButton");
      $(".sign__form-body").append(
        '<div class="sign__form-input"><input type="text" id="new_user" /><input type="text" id="new_company" /><input type="text" id="new_email" /></div>'
      );
      updateLanguage(language);
    }
  });

  $("#languageSwitcher").click(function () {
    if (language === "rus") {
      language = "eng";
    } else if (language === "eng") {
      language = "rus";
    }
    updateLanguage(language);
  });
});
