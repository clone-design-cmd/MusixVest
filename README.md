# MusixVest

MusixVest is a fictional web platform (school group project) that lets fans invest in songs. Users buy "SongShares" — fractional shares in a song's royalties — and earn a return as the song generates income.

This is currently a **static frontend prototype** (HTML, Tailwind CSS, Alpine.js). There is no backend or database yet — forms link between pages but don't save or process real data.

## How it's supposed to work

1. **Register** – a new user creates an account.
2. **Verify** – the user verifies their identity (KYC-style form: name, DOB, SSN, address, etc.).
3. **Dashboard** – once verified, the user lands on their dashboard showing portfolio value, owned SongShares, royalties, and recent transactions.
4. **Invest** – users deposit funds and buy shares in songs listed under "Offerings."
5. **Admin (planned)** – an admin role will be able to upload new songs and list them as offerings for users to invest in. This part is not built yet.

## Pages

| Page | File | Description |
|---|---|---|
| Home | `index.html` | Landing page — intro to MusixVest |
| About | `about.html` | About the platform |
| How It Works | `how-it-works.html` | Explains the investing process / FAQs |
| Offerings | `offerings.html` | Browse songs available to invest in |
| Register | `register.html` | Create an account |
| Verify | `verify.html` | Identity verification form |
| Login | `login.html` | Log in |
| Dashboard | `dashboard.html` | Investor portal — portfolio, holdings, transactions |
| Settings | `settings.html` | Account and payment settings |

## Tech stack

- HTML
- [Tailwind CSS](https://tailwindcss.com/) (via CDN)
- [Alpine.js](https://alpinejs.dev/) (via CDN, for small interactive bits like menus)
- Google Fonts (Inter)

No build step, no backend, no database — yet.

## Running it locally

Since it's just static HTML files, you can either:

- Open any `.html` file directly in your browser, or
- Run a simple local server from the project folder, e.g.:

```bash
python3 -m http.server 8000
```

Then visit `http://localhost:8000/index.html`.

## Planned next steps

- Backend and database to actually store users, songs, shares, and transactions
- Real authentication (register/verify/login currently just link to the next page)
- Admin panel for uploading songs and managing offerings
- Real payment/deposit handling

## Disclaimer

This is a school project for demonstration purposes only. It is not a real investment platform and does not offer real financial or investment services.
