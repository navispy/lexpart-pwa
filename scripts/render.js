const homeScreen = $("#pageMain");
let isDocs = false;
let hasConnection = true;
let answer = {};
let currentPage = homeScreen;

const inputHandler = (fieldName, fieldValue = "", type) => {
  answer[fieldName] = fieldValue;
  console.log(answer);
};

const generateDocs = (data) => {
  $("#addButton").after('<div class="docs" id="docs"></div>');

  if (hasConnection) {
    data.forEach((dataObject) => {
      if (dataObject.ID !== "" && dataObject.Name !== "") {
        $("#docs").append(
          `<button class="doc__button" id="docButton"><div class="doc__section"><img src="images/command_new_document_color.svg" alt="new document"><p>${dataObject.Name}</p></div></button>`
        );
        document.getElementById("docButton").ondblclick = () =>
          getQuestions(dataObject.ID).then((questions) => {
            generateQuestions(JSON.parse(questions));
          });
      }
    });
  } else {
    $("#docs").prepend('<p class="doc__error">Нет соединения с сервером</p>');
  }
};

const generateSection = (data) => {
  $("#addButton").after('<div class="docs-list" id="docsList"></div>');

  if (hasConnection) {
    $("#docsList").prepend(
      "<div class='docs-list__header'><p>Номер</p><p>Документ</p></div>"
    );

    data.forEach((dataObject) => {
      if (dataObject.ID !== "" && dataObject.Name !== "") {
        $("#docsList").append(
          `<button class="docs-list__button" id="docsListButton"><div class="docs-list__section"><p>${dataObject.ID}</p><p>${dataObject.Name}</p></div></button>`
        );
        document.getElementById("docsListButton").ondblclick = () =>
          getQuestions(dataObject.ID).then((questions) => {
            // console.log(JSON.parse(questions)); //all data
            generateQuestions([
              JSON.parse(questions)[13],
              JSON.parse(questions)[0],
            ]);
          });
      }
    });
  } else {
    $("#docsList").append(
      '<p class="docs-list__errors">Нет соединения с сервером</p>'
    );
  }
};

const createNewDocs = () => {
  isDocs = !isDocs;

  if (isDocs) {
    $("#docsList").detach();
    getSections()
      .then((data) => {
        generateDocs(data);
      })
      .catch((reason) => {
        console.log("mistake", reason);
        hasConnection = false;
        generateDocs();
      });
  } else {
    $("#docs").detach();
    generateSection([{ ID: "1", Name: "some doc" }]);
  }
};

const generateQuestions = (questionsArray) => {
  homeScreen.detach();
  let currentQuestion = 0;
  let currFieldType = [];
  let currFieldName = [];
  let hasAnswers = false;
  let tableQuestions = {};
  let selectedTableList = [];
  let buttonsOnQuestions = 0;

  const updateId = (id) => {
    return $(`#${id}`).length ? `${id}1` : id;
  };

  const generateTable = () => {
    const tableAnswers = {};
    let tableItem = "";
    tableQuestions.DetailFields.forEach((tableQuestion, j) => {
      let id = updateId(
        tableQuestion.FieldName + j + answer[tableQuestions.FieldName].length
      );

      if (currFieldType.length === 0) {
        currFieldType.push(tableQuestion.FieldType);
        currFieldName.push(tableQuestion.FieldName);
      }

      tableAnswers[tableQuestion.FieldName] = "";

      tableItem += `<div class='question-table__item'><p>${tableQuestion.FieldText}</p>
          <input id=${id} type="text"/></div>`;
    });

    if (
      answer.hasOwnProperty(tableQuestions.FieldName) ||
      answer[tableQuestions.FieldName] === ""
    ) {
      answer[tableQuestions.FieldName] = [
        ...answer[tableQuestions.FieldName],
        tableAnswers,
      ];
    }

    return `<div class="question-table__list" id = ${updateId(
      `question-table__list${answer[tableQuestions.FieldName].length - 1}`
    )}>${tableItem}</div>`;
  };

  const generateButton = (
    id,
    buttonsValue = {
      firstValue: "Запись 1",
      secondValue: "Запись 2",
      tValue: "Запись 3",
    }
  ) => {
    let buttons = ``;
    let idCount = 0;

    for (let key in buttonsValue) {
      idCount++;
      buttons += `<button id=${id + idCount}>${buttonsValue[key]}</button>`;
    }
    buttonsOnQuestions = idCount;

    return `<div class="question-answer__buttons ${
      buttonsOnQuestions > 3 ? "dropdown__menu" : ""
    }">${buttons}</div>${
      buttonsOnQuestions > 3
        ? '<div class="dropdown-menu__button"><button id="dropdownMenuButton">Развернуть</button></div>'
        : ""
    }`;
  };

  $("main").append(
    `<div id="questionPage" class="question-page"><div class="question-page__nav-buttons"><button id='back-button'>Назад</button><button id='save-button' style='display: none'>Cформировать</button><button id='next-button'>Вперед</button></div></div>`
  );

  const inputValidation = (fieldType, id) => {
    if (fieldType === "7") {
      $(`#${id}`).inputmask("99.99.9999", {
        placeholder: "ДД:ММ:ГГГГ",
        showMaskOnHover: false,
      });
    } else if (fieldType === "1") {
      $(`#${id}`).inputmask({ alias: "numeric" });
    }
  };

  const handlerSettings = () => {
    currentPage = $("#questionPage");
    $("#dropdownMenuButton").click(function () {
      $(".question-answer__buttons").toggleClass("dropdown-menu");
      $(this).text() === "Развернуть"
        ? $(this).text("Свернуть")
        : $(this).text("Развернуть");
    });

    currFieldName.forEach((fieldName, i) => {
      let id = fieldName + i;

      inputValidation(currFieldType[i], id);

      typeof answer[fieldName] === "number"
        ? $(`#${id + answer[fieldName]}`).addClass("clicked")
        : null;

      for (let count = 1; count < buttonsOnQuestions + 1; count++) {
        $(`#${id + count}`).click(function () {
          $(`.question-answer__buttons`)
            .find(".clicked")
            .removeClass("clicked");
          $(this).toggleClass("clicked");
          inputHandler(fieldName, count, currFieldType[i]);
        });
      }

      if (currFieldType[i] === "6") {
        const tableAnswers = answer[fieldName];

        for (
          let count = 0;
          count <= answer[tableQuestions.FieldName].length - 1;
          count++
        ) {
          for (let j = 0; j < currFieldName.length - 1; j++) {
            let id = $(`#${currFieldName[i + 1 + j] + j + count + 1}`).length
              ? updateId(currFieldName[i + 1 + j] + j + count)
              : currFieldName[i + 1 + j] + j + count;
            let tableAnswersObj = tableAnswers[count];

            inputValidation(currFieldType[i + 1 + j], id);

            $(`#${id}`)
              .val(
                `${
                  tableAnswersObj.hasOwnProperty(currFieldName[i + 1 + j])
                    ? tableAnswersObj[currFieldName[i + 1 + j]]
                    : ""
                }`
              )
              .change(function () {
                tableAnswersObj[currFieldName[i + j + 1]] = $(this).val();
              });
          }
        }

        inputHandler(fieldName, tableAnswers, currFieldType[i]);
      } else
        $(`#${id}`)
          .val(`${answer[fieldName]}`)
          .change(() =>
            inputHandler(fieldName, $(`#${id}`).val(), currFieldType[i])
          );
    });
  };

  const selectTableQuestion = (plusButton = false) => {
    const tableListHandler = (num) => {
      let id = $(`#question-table__list${num}1`).length
        ? updateId(`question-table__list${num}`)
        : `question-table__list${num}`;

      $(`#${id}`).click(function () {
        $(this).toggleClass("selected");
        selectedTableList.every((tableList) => tableList.id !== `#${id}`)
          ? selectedTableList.push({
              id: `#${id}`,
              number: num,
            })
          : (selectedTableList = selectedTableList.filter(
              (item) => item.id !== `#${id}`
            ));
      });
    };

    plusButton
      ? tableListHandler(answer[tableQuestions.FieldName].length - 1)
      : answer[tableQuestions.FieldName].forEach((item, count) => {
          tableListHandler(count);
        });
  };

  const buttonsSettings = () => {
    selectedTableList = [];

    $("#plusButton").click(() => {
      $(".question-table__main-list").append(`${generateTable()}`);
      selectTableQuestion(true);
      handlerSettings();
    });

    $("#minusButton").click(() => {
      selectedTableList.forEach((tableList) => {
        $(tableList.id).detach();
        answer[tableQuestions.FieldName].splice(tableList.number, 1);
        selectedTableList = selectedTableList.filter(
          (item) => item.id !== tableList.id
        );
      });
    });
  };

  const generateQuestionHtml = (questionNum) => {
    let html = "";
    currFieldType = [];
    currFieldName = [];

    if (questionNum === questionsArray.length - 1) {
      $("#next-button").css("display", "none");
      $("#save-button").css("display", "inline-block");
    } else {
      $("#next-button").css("display", "inline-block");
      $("#save-button").css("display", "none");
    }

    questionsArray[questionNum].forEach((question, i) => {
      currFieldType.push(question.FieldType);
      currFieldName.push(question.FieldName);

      if (!answer.hasOwnProperty(question.FieldName)) {
        answer[question.FieldName] = "";
      }

      let id = question.FieldName + i;

      if (question.FieldType === "3") {
        const buttons = generateButton(id);

        html += `<div id='question' class="question">
        <p>${question.FieldText}</p>
        ${buttons}</div>`;
      } else if (question.FieldType === "6") {
        let tableList = "";
        const tableAnswers = [];

        tableQuestions = question;

        question.DetailFields.forEach((data) => {
          currFieldType.push(data.FieldType);
          currFieldName.push(data.FieldName);
        });

        for (
          let count = 0;
          count <= answer[tableQuestions.FieldName].length - 1;
          count++
        ) {
          let tableItem = "";

          tableAnswers.push({});

          question.DetailFields.forEach((tableQuestion, j) => {
            let id = tableQuestion.FieldName + j + count;
            const tableAnswersObj = tableAnswers[count];

            tableAnswersObj[tableQuestion.FieldName] = "";

            tableItem += `<div class='question-table__item'><p>${tableQuestion.FieldText}</p>
          <input id=${id} type="text"/></div>`;
          });

          tableList += `<div class='question-table__list' id='question-table__list${count}'>${tableItem}</div>`;
        }

        html += `<div id='question' class="question"><p>${question.FieldText}</p>
        <div class="question-table">
        <div class="question-table__buttons">
        <button id="plusButton" class="question-table__button-plus">+</button><button id="minusButton" class="question-table__button-minus">-</button>
        </div>
        <div class="question-table__main-list">
        ${tableList}
        </div>
        </div>
        </div>`;

        if (
          !answer.hasOwnProperty(tableQuestions.FieldName) ||
          answer[tableQuestions.FieldName] === ""
        ) {
          answer[tableQuestions.FieldName] = tableAnswers;
        }
      } else {
        html += `<div id='question' class="question"><p>${question.FieldText}</p>
        <input id=${id} type="text"/></div>`;
      }
    });

    return `<div id="questions" class="questions">${html}</div>`;
  };

  $("#questionPage").prepend(`${generateQuestionHtml(currentQuestion)}`);

  handlerSettings();
  buttonsSettings();

  $("#back-button").click(() => {
    currentQuestion--;

    if (currentQuestion < 0) {
      currentPage.detach();
      homeScreen.appendTo("main");
    } else {
      $("#questions").replaceWith(generateQuestionHtml(currentQuestion));
      buttonsSettings();
      handlerSettings();
    }
  });
  $("#next-button").click(() => {
    currentQuestion++;

    if (currentQuestion < questionsArray.length) {
      $("#questions").replaceWith(generateQuestionHtml(currentQuestion));
      buttonsSettings();
      handlerSettings();
    }
  });
  $("#save-button").click(() => {
    hasAnswers = true;

    for (let key in answer) {
      if (typeof answer[key] === "object" && answer[key].length !== 0) {
        answer[key].forEach((ans) => {
          for (let keyInTable in ans) {
            if (ans[keyInTable] === "") {
              hasAnswers = false;
            }
          }
        });
      } else {
        if (answer[key] === "" || answer[key].length === 0) {
          hasAnswers = false;
        }
      }
    }

    if (hasAnswers) {
      currentPage.detach();
      homeScreen.appendTo("main");
      alert("Документ сформирован");
      console.log("Документ сформирован", JSON.stringify(answer));
    } else alert("Дайте ответ на все вопросы");
  });
};

if (isDocs) {
  getSections()
    .then((data) => {
      generateDocs(data);
    })
    .catch((reason) => {
      console.log("mistake", reason);
      hasConnection = false;
      generateDocs();
    });
} else generateSection([{ ID: "1", Name: "some doc" }]);

$("#homeButton").click(() => {
  currentPage.detach();
  homeScreen.appendTo("main");
});
