# Goitom Finance — Functioneel Requirements & User Stories

Versie: 2025-09-30
Eigenaar: Goitom Finance (Habesha freelancers & ondernemers)
Status: Draft (MVP scope vastgesteld)

## 1. Doel & Scope
- Moderne, cultureel passende boekhoudtool voor Habesha freelancers en kleine ondernemingen.
- Webapp (responsive) als startpunt; mobile app later.
- Focus op eenvoud, meertaligheid (EN/NL/TI/AM), en professionele facturatie.

## 2. Persona’s (samenvatting)
- Freelancer: snelle facturatie, inzicht in betalingen, eenvoudige uitgaven.
- Small Business Owner: projecten, team & rapportages.
- Consultant: meertalige documenten, betrouwbare archivering.

## 3. Prioriteitenlegenda
- Must-Have (MVP)
- Should-Have (V2)
- Nice-to-Have (Future)

## 4. Feature-overzicht (t.o.v. e‑Boekhouden.nl)

| Feature | e‑Boekhouden.nl | Goitom Finance | Prioriteit |
| --- | --- | --- | --- |
| Dashboard (KPIs, charts, balances) | ✅ | ✅ | Must-Have (MVP) |
| Clients / Relationship Management | ✅ | ✅ | Must-Have (MVP) |
| Invoices (Facturen) | ✅ | ✅ | Must-Have (MVP) |
| Quotations / Offers (Offertes) | ✅ | ➕ Planned | Should-Have (V2) |
| Expenses (Uitgaven) | ✅ | ✅ | Must-Have (MVP) |
| Projects | ✅ | ✅ | Must-Have (MVP) |
| Time Tracking (Urenregistratie) | ✅ | ➕ Planned | Should-Have (V2) |
| Payments (Betalingen) | ✅ | ✅ | Must-Have (MVP) |
| Recurring Invoices / Subscriptions | ✅ | ➕ Planned | Should-Have (V2) |
| Automatic Reminders (Overdue) | ✅ | ➕ Planned | Should-Have (V2) |
| Custom Invoice Fields | ✅ | ➕ Planned | Should-Have (V2) |
| Company Branding (logo/kleuren) | ✅ | ➕ Planned | Must-Have (MVP) |
| Reports & Analytics | ✅ | ✅ (financiële rapporten) | Must-Have (MVP) |
| Digital Archive (Docs, Receipts) | ✅ | ➕ Planned (S3/local) | Should-Have (V2) |
| Scan & Recognize (OCR) | ✅ | ➕ Planned | Nice-to-Have (Future) |
| Import/Export (CSV/Excel) | ✅ | ➕ Planned | Should-Have (V2) |
| Bank Integrations | ✅ | ➕ Planned | Nice-to-Have (Future) |
| Payment Gateways (Mollie/Stripe/PayPal) | ✅ | ➕ Planned | Should-Have (V2) |
| Multi-Language | ❌ beperkt | ✅ (EN/NL/TI/AM) | Should-Have (V2) |
| Mobile App | ✅ | ✅ responsive (app later) | Must-Have (MVP) |

## 5. Functionele Requirements (MVP)
### 5.1 Dashboard
- Toon totalen: openstaande facturen, ontvangen betalingen, uitgaven, saldo (afgeleid).
- Grafieken: omzet per maand (bar), top-klanten (list), cash-in/out (line).
- Filters: periode (maand/kwartaal/jaar), klant.
- Acceptatie: cijfers en grafieken laden < 1.5s met 3k records.

### 5.2 Clients (CRM)
- CRUD klanten; velden: naam, bedrijf, btw/kvk (optioneel), e‑mail, telefoon, adres, taal.
- Relaties: klant → facturen, offertes, projecten.
- Zoeken/filters, soft delete, import vCard/CSV later (V2).
- Acceptatie: aanmaken en gebruiken in factuur-flow zonder paginarefresh.

### 5.3 Invoices
- Factuurtemplate met branding (logo), nummerreeks, valuta (EUR), btw-rates (0/9/21 NL).
- Status: draft, sent, partial, paid, overdue, void.
- Download PDF, e‑mail verzending (in-app), meertalige layout (EN/NL; TI/AM V2).
- Acceptatie: PDF consistent, sommen correct met ronde‑regels, btw uitgesplitst.

### 5.4 Expenses
- CRUD uitgaven; categorieën (dynamic), btw-rates, bijlage upload (jpg/pdf/png).
- Acceptatie: upload ≤ 5MB, totalen per maand/jaar correct.

### 5.5 Projects
- CRUD projecten; relatie klant; budget (uur/bedrag); status (open/afgerond).
- Overzicht gekoppelde facturen/uren (urenregistratie V2).

### 5.6 Payments
- Registratie van ontvangen betalingen op facturen (datum, bedrag, methode).
- Deelbetalingen; automatisch statusupdate.

### 5.7 Reports (basis)
- Omzet per periode; uitgaven per categorie; winst/verlies rudimentair.
- Export PDF (CSV/Excel in V2).

### 5.8 Branding (MVP subset)
- Upload logo; kies accenten (brand‑kleur toegepast op UI/pdf); bedrijfsgegevens op documenten.

### 5.9 Meertaligheid (MVP scope)
- UI strings klaargezet (EN/NL actief); TI/AM tekstbestanden voorbereid (v2 rollout).

### 5.10 Security & Roles
- Auth (Laravel + Sanctum).
- Rollen: Owner, Member (beperkte CRUD).
- Audit logging basis (model events) — minimaal factuur/betaling mutaties.

## 6. Functionaliteiten V2 (Should‑Have)
- Offertes (convert to invoice), Urenregistratie, Recurring invoices, Overdue reminders,
  Custom invoice fields, Import/Export, Payment gateways (Mollie/Stripe/PayPal),
  Volledige meertaligheid (EN/NL/TI/AM), Documentarchief (S3/Local, tagging).

## 7. Future (Nice‑to‑Have)
- OCR voor bonnetjes, Bankkoppelingen (PSD2), Mobile apps, AI‑inzichten (cashflow forecast, anomalieën).

## 8. Niet‑functionele Requirements
- Performance: P95 < 300ms API; lijstweergaven met pagination; SSR/Vite build geoptimaliseerd.
- Beveiliging: OWASP best‑practices, CSRF, rate‑limits, encryptie at rest voor gevoelige data.
- Privacy/Compliance: AVG; dataretentie en verwijdering; export van persoonsgegevens (V2).
- Observability: structured logging; error tracking (Sentry) in productie; health endpoint.

## 9. Datamodel (hoog niveau)
- Client(id, name, email, phone, address, locale)
- Project(id, client_id, name, budget_amount, status)
- Invoice(id, client_id, number, issue_date, due_date, currency, status, totals)
- InvoiceItem(id, invoice_id, description, qty, unit_price, vat_rate)
- Expense(id, category_id, date, amount, vat_rate, attachment_path)
- Payment(id, invoice_id, amount, paid_at, method, note)
- User(id, name, email, role)

Relaties: client 1‑N invoices/projects; invoice 1‑N items/payments.

## 10. API (indicatief)
- CRUD: /clients, /projects, /invoices, /expenses, /payments
- POST /invoices/:id/send, GET /invoices/:id/pdf
- Auth: Sanctum; alle endpoints auth‑only.

## 11. User Stories (selectie)
- Als freelancer wil ik in < 60s een factuur maken, zodat ik sneller betaald word. (MVP)
- Als gebruiker wil ik mijn logo op facturen tonen, zodat mijn merk consistent is. (MVP)
- Als eigenaar wil ik deelbetalingen registreren, zodat openstaand saldo klopt. (MVP)
- Als gebruiker wil ik uitgaven met btw registreren, zodat rapportages kloppen. (MVP)
- Als manager wil ik omzet en kosten per maand zien, zodat ik kan sturen. (MVP)
- Als gebruiker wil ik een offerte kunnen omzetten naar factuur. (V2)
- Als freelancer wil ik uren aan projecten registreren en factureren. (V2)
- Als gebruiker wil ik CSV/Excel exporteren/importeren. (V2)

## 12. Acceptatiecriteria (voorbeelden)
- Factuur totalen: Σ(items.qty × unit_price) + Σ(btw) = grand_total; afronden op €0,01.
- PDF weergave: A4, marges 18–24mm, lettertype consistent, logo zichtbaar ≤ 256KB.
- Betaling: bij registratie wordt invoice.status automatisch updated (partial/paid).
- Meertaligheid: UI kan EN/NL omschakelen zonder herbouw; locale per gebruiker.

## 13. MVP Checklist
- [ ] Dashboard (KPI’s + 2 grafieken)
- [ ] Clients CRUD + zoek
- [ ] Invoices + PDF + e‑mail
- [ ] Payments (deelbetalingen)
- [ ] Expenses CRUD + upload
- [ ] Projects CRUD
- [ ] Reports (3 basisrapporten)
- [ ] Branding (logo + kleur)
- [ ] Auth & roles; logging basis
- [ ] Responsive UI + dark/light

## 14. Uitrol & Ops
- Environments: dev/stage/prod; .env per omgeving; migrations semver + datum.
- Backups: dagelijks DB + bijlagen (S3 bucket versioning); retentie 30 dagen.
- Monitoring: uptime check; error tracking; logging naar central store.

## 15. Roadmap (samenvatting)
- Fase 1 (MVP, 4–6 weken): sectie 5 + checklist.
- Fase 2 (V2, 6–10 weken): sectie 6.
- Future: sectie 7.

---

Laat weten of ik dit moet koppelen aan bestaande issues/epics of vertalen naar Jira‑tickets.


