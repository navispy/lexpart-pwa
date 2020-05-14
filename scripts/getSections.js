const url = "../../skyforce/jurbot-api/get_sections.php";

async function getSections() {
  const response = await fetch(url);

  const json = await response.json();
  return json;
}

