const homeScreen = $("#page-main");
let isDocs = false;
let hasConnection = true;
let answer = {};
let currentPage = $("#page-main");

const inputHandler = (fieldName, fieldValue = "", type) => {
  answer[fieldName] = fieldValue;
  console.log(answer);
};

const generateDocs = (data) => {
  const addButton = document.getElementById("add-button");
  let divDocs = document.createElement("div");

  divDocs.className = "docs";
  divDocs.id = "docs";

  addButton.after(divDocs);

  if (hasConnection) {
    const revData = data.reverse();

    revData.forEach((dataObject) => {
      if (dataObject.ID !== "" && dataObject.Name !== "") {
        let div = document.createElement("div");
        let img = document.createElement("img");
        let pName = document.createElement("p");
        let button = document.createElement("button");

        div.className = "doc-section";
        img.src = "./images/command_new_document_color.svg";
        img.alt = "new document";
        pName.innerHTML = `${dataObject.Name}`;

        button.className = "doc-button";
        button.id = "doc-button";
        button.ondblclick = () =>
          getQuestions(dataObject.ID).then((questions) => {
            generateQuestions(JSON.parse(questions));
          });

        button.prepend(div);
        div.prepend(img);
        img.after(pName);

        divDocs.prepend(button);
      }
    });
  } else {
    let p = document.createElement("p");

    p.innerHTML = "Нет соединения с сервером";

    p.className = "doc-errors";

    addButton.after(divDocs);
    divDocs.prepend(p);
  }
};

const generateSection = (data) => {
  const addButton = document.getElementById("add-button");
  let divSections = document.createElement("div");

  divSections.className = "docs-list";
  divSections.id = "docs-list";

  if (hasConnection) {
    const revData = data.reverse();

    let divSectionsHeader = document.createElement("div");
    let pNumber = document.createElement("p");
    let pDoc = document.createElement("p");

    divSectionsHeader.className = "docs-list__header";

    pNumber.innerHTML = "Номер";
    pDoc.innerHTML = "Документ";

    addButton.after(divSections);
    divSections.prepend(divSectionsHeader);
    divSectionsHeader.prepend(pNumber);
    pNumber.after(pDoc);

    revData.forEach((dataObject) => {
      if (dataObject.ID !== "" && dataObject.Name !== "") {
        let div = document.createElement("div");
        let pId = document.createElement("p");
        let pName = document.createElement("p");
        let button = document.createElement("button");

        button.className = "docs-list__button";
        button.id = "docs-list__button";
        button.ondblclick = () =>
          getQuestions(dataObject.ID).then((questions) => {
            // console.log(JSON.parse(questions)); //all data
            generateQuestions([
              JSON.parse(questions)[12],
              JSON.parse(questions)[0],
            ]);
          });

        div.className = "docs-list__section";

        pId.innerHTML = `${dataObject.ID}`;
        pName.innerHTML = `${dataObject.Name}`;

        button.prepend(div);
        div.prepend(pId);
        pId.after(pName);

        divSectionsHeader.after(button);
      }
    });
  } else {
    let p = document.createElement("p");

    p.innerHTML = "Нет соединения с сервером";

    p.className = "docs-list__errors";

    addButton.after(divSections);
    divSections.prepend(p);
  }
};

const createNewDocs = () => {
  const divSections = document.getElementById("docs-list");
  const divDocs = document.getElementById("docs");
  isDocs = !isDocs;

  if (isDocs) {
    divSections.remove();
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
    divDocs.remove();
    generateSection([{ ID: "1", Name: "some doc" }]);
  }
};

const generateQuestions = (questionsArray) => {
  homeScreen.detach();
  let currentQuestion = 0;
  let currFieldType = [];
  let currFieldName = [];
  let hasAnswers = false;

  const generateButton = (
    id,
    buttonsValue = { firstValue: "Да", secondValue: "Нет" }
  ) => {
    let buttons = ``;
    let idCount = 1;

    for (let key in buttonsValue) {
      buttons += `<button id=${id + idCount}>${buttonsValue[key]}</button>`;

      idCount++;
    }
    return `<div class="question__answer_buttons">${buttons}</div>`;
  };

  const chooseButtons = (type, id) => {
    switch (type) {
      case "navButtons":
        return "<div class=\"question-page__nav-buttons\"><button id='back-button'>Назад</button><button id='save-button' style='display: none'>Cформировать</button><button id='next-button'>Вперед</button></div>";
      case "Boolean":
        return generateButton(id);
      case "CalendarOrBankDays":
        return generateButton(id, {
          1: "Календарных дней",
          2: "Банковских дней",
        });
      case "NoPaymentOrPartial":
        return generateButton(
          id,
          "Оплата произведена частично",
          "Оплата не производилась"
        );
    }
  };

  $("main").append(
    `<div id="question-page" class="question-page">
     ${chooseButtons("navButtons")}</div>`
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
    currentPage = $("#question-page");

    currFieldName.forEach((fieldName, i) => {
      let id = fieldName + i;

      inputValidation(currFieldType[i], id);

      $(`#${id + 1}`).click(() => inputHandler(fieldName, 1, currFieldType[i]));
      $(`#${id + 2}`).click(() => inputHandler(fieldName, 2, currFieldType[i]));

      if (currFieldType[i] === "6") {
        const tableAnswers =
          answer.hasOwnProperty(fieldName) && answer[fieldName] !== [{}]
            ? answer[fieldName]
            : [{}];

        for (let j = 0; j < currFieldName.length - 1; j++) {
          let id = currFieldName[i + 1 + j] + j;

          inputValidation(currFieldType[i + 1 + j], id);

          $(`#${id}`)
            .val(
              `${
                tableAnswers[0].hasOwnProperty(currFieldName[i + 1 + j])
                  ? tableAnswers[0][currFieldName[i + 1 + j]]
                  : ""
              }`
            )
            .change(
              () =>
                (tableAnswers[0][currFieldName[i + j + 1]] = $(`#${id}`).val())
            );
        }

        inputHandler(fieldName, tableAnswers, currFieldType[i]);
      } else
        $(`#${id}`)
          .val(`${answer.hasOwnProperty(fieldName) ? answer[fieldName] : ""}`)
          .change(() =>
            inputHandler(fieldName, $(`#${id}`).val(), currFieldType[i])
          );
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
        const buttons =
          "LookupSubstitute" in question
            ? generateButton(id, question["LookupSubstitute"])
            : chooseButtons(question.LookupTable, id); // Тут происходит генерация списка кнопок, функция generateButton генерирует спискок ответ (принимает id и обьект вида {Любое название:"значение которое тут написано будет выведено в списке ответо"}

        html += `<div id='question' class="question">
        <p>${question.FieldText}</p>
        ${buttons}</div>`;
      } else if (question.FieldType === "6") {
        let tableList = "";
        const tableAnswers = [{}];

        question.DetailFields.forEach((tableQuestion, j) => {
          let id = tableQuestion.FieldName + j;

          currFieldType.push(tableQuestion.FieldType);
          currFieldName.push(tableQuestion.FieldName);

          tableAnswers[0][tableQuestion.FieldName] = "";

          tableList += `<div class='question-table__item'><p>${tableQuestion.FieldText}</p>
          <input id=${id} type="text"/></div>`;
        });

        html = `<div id='question' class="question"><p>${question.FieldText}</p>
        <div class="question-table">
        <div class="question-table__buttons">
        <button id="plusButton" class="question-table__button-plus">+</button><button id="minusButton" class="question-table__button-minus">-</button>
        </div>
        <div class="question-table__list">${tableList}</div>
        </div>
        </div>`;
        if (
          !answer.hasOwnProperty(question.FieldName) ||
          answer[question.FieldName] === ""
        ) {
          answer[question.FieldName] = tableAnswers;
        }
      } else {
        html += `<div id='question' class="question"><p>${question.FieldText}</p>
        <input id=${id} type="text"/></div>`;
      }
    });

    return `<div id="questions" class="questions">${html}</div>`;
  };

  $("#question-page").prepend(`${generateQuestionHtml(currentQuestion)}`);

  handlerSettings();

  $("#back-button").click(() => {
    currentQuestion--;

    if (currentQuestion < 0) {
      currentPage.detach();
      homeScreen.appendTo("main");
    } else {
      $("#questions").replaceWith(generateQuestionHtml(currentQuestion));
      handlerSettings();
    }
  });
  $("#next-button").click(() => {
    currentQuestion++;

    if (currentQuestion < questionsArray.length) {
      $("#questions").replaceWith(generateQuestionHtml(currentQuestion));
      handlerSettings();
    }
  });
  $("#save-button").click(() => {
    hasAnswers = true;

    for (let key in answer) {
      if (typeof answer[key] === "object") {
        answer[key].forEach((ans) => {
          for (let keyInTable in ans) {
            if (ans[keyInTable] === "") {
              hasAnswers = false;
            }
          }
        });
      } else {
        if (answer[key] === "") {
          hasAnswers = false;
        }
      }
    }
    console.log(hasAnswers);
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
