let isDocs = false;
let hasConnection = true;
let answer = {};

const inputHandler = (fieldName, fieldValue = "", type) => {
  answer[fieldName] = fieldValue;
  console.log(answer);
};

const generateDocs = () => {
  const addButton = document.getElementById("add-button");
  let divDocs = document.createElement("div");

  divDocs.className = "docs";
  divDocs.id = "docs";

  addButton.after(divDocs);

  for (let i = 5; i >= 0; i--) {
    let div = document.createElement("div");
    let img = document.createElement("img");
    let pName = document.createElement("p");

    div.className = `doc${i + 1}`;
    img.src = "./images/command_new_document_color.svg";
    img.alt = "new document";
    pName.innerHTML = "doc";

    div.prepend(img);
    img.after(pName);

    divDocs.prepend(div);
  }
};

const generateSection = data => {
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

    revData.forEach(dataObject => {
      if (dataObject.ID !== "" && dataObject.Name !== "") {
        let div = document.createElement("div");
        let pId = document.createElement("p");
        let pName = document.createElement("p");
        let button = document.createElement("button");

        button.className = "docs-list__button";
        button.id = "docs-list__button";
        button.ondblclick = () =>
          getQuestions(dataObject.ID).then(questions => {
            generateQuestions(JSON.parse(questions));
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
    generateDocs();
  } else {
    divDocs.remove();
    getSections()
      .then(data => {
        generateSection(data);
      })
      .catch(reason => {
        console.log("mistake", reason);
        hasConnection = false;
        generateSection();
      });
  }
};

const generateQuestions = questionsArray => {
  const homeScreen = $("#page-main").detach();
  let currentQuestion = 0;
  let currFieldType = [];
  let currFieldName = [];

  const generateButton = (id, firstValue = "Да", secondValue = "Нет") =>
    `<div class="question__answer_buttons"><button id=${id +
      1}>${firstValue}</button><button id=${id +
      2}>${secondValue}</button></div>`;

  const chooseButtons = (type, id) => {
    switch (type) {
      case "navButtons":
        return "<div class=\"question__nav_buttons\"><button id='back-button'>Назад</button><button id='save-button' style='display: none'>Cформировать</button><button id='next-button'>Вперед</button></div>";
      case "Boolean":
        return generateButton(id);
      case "CalendarOrBankDays":
        return generateButton(id, "Календарных дней", "Банковских дней");
      case "NoPaymentOrPartial":
        return generateButton(
          id,
          "Оплата произведена частично",
          "Оплата не производилась"
        );
    }
  };

  const updatePage = () => {
    currFieldName.forEach((fieldName, i) => {
      let id = fieldName + i;

      $(`#${id + 1}`).click(() => inputHandler(fieldName, 1, currFieldType[i]));
      $(`#${id + 2}`).click(() => inputHandler(fieldName, 2, currFieldType[i]));
      if (currFieldType[i] === "6") {
        const tableAnswers = answer.hasOwnProperty(fieldName)
          ? answer[fieldName]
          : {};

        for (let j = 0; j < currFieldName.length - 1; j++) {
          let id = currFieldName[i + 1 + j] + j;

          $(`#${id}`)
            .val(
              `${
                tableAnswers.hasOwnProperty(currFieldName[i + 1 + j])
                  ? tableAnswers[currFieldName[i + 1 + j]]
                  : ""
              }`
            )
            .change(
              () => (tableAnswers[currFieldName[i + j + 1]] = $(`#${id}`).val())
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

  const generateQuestionHtml = questionNum => {
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

      let id = question.FieldName + i;

      if (question.FieldType === "3") {
        html += `<div id='question' class="question">
        <p>${question.FieldText}</p>
        ${chooseButtons(question.LookupTable, id)}</div>`;
      } else if (
        question.FieldType === "2" ||
        question.FieldType === "1" ||
        question.FieldType === "7"
      ) {
        html += `<div id='question' class="question"><p>${question.FieldText}</p>
        <input id=${id} type="text"/></div>`;
      } else if (question.FieldType === "6") {
        let table = "";

        question.DetailFields.forEach((tableQuestion, j) => {
          let id = tableQuestion.FieldName + j;

          currFieldType.push(tableQuestion.FieldType);
          currFieldName.push(tableQuestion.FieldName);

          table += `<div class='question-table'><p>${tableQuestion.FieldText}</p>
          <input id=${id} type="text"/></div>`;
        });

        html = `<div id='question' class="question"><p>${question.FieldText}</p>
        ${table}</div>`;
      }
    });

    return `<div id="questions" class="questions">${html}</div>`;
  };

  $("main").append(
    `<div id="question-page" class="question-page">
     ${generateQuestionHtml(currentQuestion)}
     ${chooseButtons("navButtons")}</div>`
  );

  updatePage();

  $("#back-button").click(() => {
    currentQuestion--;

    if (currentQuestion < 0) {
      $("#question-page").detach();
      homeScreen.appendTo("main");
    } else {
      $("#questions").replaceWith(generateQuestionHtml(currentQuestion));
      updatePage();
    }
  });
  $("#next-button").click(() => {
    currentQuestion++;

    if (currentQuestion < questionsArray.length) {
      $("#questions").replaceWith(generateQuestionHtml(currentQuestion));
      updatePage();
    }
  });
  $("#save-button").click(() => {
    $("#question-page").detach();
    homeScreen.appendTo("main");
    console.log("Документ сформирован", answer);
  });
};

if (isDocs) {
  generateDocs();
} else
  getSections()
    .then(data => {
      generateSection(data);
    })
    .catch(reason => {
      console.log("mistake", reason);
      hasConnection = false;
      generateSection();
    });
