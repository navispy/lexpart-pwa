let isDocs = false;
let hasConnection = true;

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

  console.log(getQuestions(data[0].ID).then(data=> console.log(data)))
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

        div.className = "docs-list__section";

        pId.innerHTML = `${dataObject.ID}`;
        pName.innerHTML = `${dataObject.Name}`;

        div.prepend(pId);
        pId.after(pName);

        divSectionsHeader.after(div);
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
