# 📘 Memora

**Memora** is a simple flashcard and spaced repetition system (SRS) built with **Laravel**.  
It helps users learn and remember words, facts, or any subject more effectively by using the power of repetition.  

---

## ✨ Features
- Create your own **decks** and **cards**  
- Import public decks shared by other users  
- Each user gets a **personal copy** of a public deck (safe from deletion by the owner)  
- Set your own **learning pace** with 
- Track your progress with **review history** (ease factor, timestamps)  
- Support for **audio (TTS)** on cards  
- Fully **user-based**: decks and progress are tied to your account  

---

## 🛠 Tech Stack
- **Backend**: Laravel  
- **Database**: MySQL / PostgreSQL  
- **Frontend**: Blade / Vue (optional, depending on your setup)  
- **Auth**: Laravel Breeze / Jetstream  

---

## 🚀 Installation

1. Clone the repository  
   ```bash
   git clone https://github.com/yourusername/memora.git
   cd memora
Install dependencies

bash
Always show details

Copy code
composer install
npm install && npm run dev
Copy .env file and set up database

bash
Always show details

Copy code
cp .env.example .env
php artisan key:generate
Run migrations

bash
Always show details

Copy code
php artisan migrate
Start the server

bash
Always show details

Copy code
php artisan serve
Now open http://127.0.0.1:8000 🎉

🧠 Spaced Repetition (SM-2 Algorithm)
Memora uses the SM-2 algorithm, the same system used in Anki.
Each card gets an ease factor and an interval that adjusts based on how well you remember it.
This ensures you review hard cards more often and easy cards less frequently.

📌 Roadmap
 Add full SM-2 spaced repetition scheduling

 Improve TTS (text-to-speech) support

 Deck sharing & collaboration

 Mobile-friendly UI

🤝 Contributing
Pull requests are welcome! For major changes, please open an issue first to discuss what you’d like to change.

📄 License
This project is open-source under the MIT License.
