const urlDB = "http://lexpart.by/skyforce/jurbot-api/get_questions.php?ID=";

async function getQuestions(id) {
  const response = await fetch(urlDB + id);

  const json = await response.json();
  return json;
}
