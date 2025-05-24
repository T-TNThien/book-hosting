const express = require("express");
const cors = require("cors");
const axios = require("axios");

const app = express();
app.use(cors());

app.get("/", async (req, res) => {
  const ttsUrl = req.query.url;
  if (!ttsUrl || !ttsUrl.startsWith("https://translate.google.com")) {
    return res.status(400).send("Invalid URL");
  }
  try {
    const response = await axios.get(ttsUrl, {
      responseType: "stream",
      headers: {
        "User-Agent": "Mozilla/5.0"
      }
    });
    res.set(response.headers);
    response.data.pipe(res);
  } catch (err) {
    res.status(500).send("TTS request failed.");
  }
});

const PORT = process.env.PORT || 3000;
app.listen(PORT, () => console.log(`TTS proxy running on port ${PORT}`));
