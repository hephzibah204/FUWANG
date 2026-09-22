import openpyxl

wb = openpyxl.load_workbook(r"C:\Users\hephz\Documents\DOC-20260922-WA0011.xlsx")
sheet = wb.active
rows = list(sheet.iter_rows(values_only=True))

inserts = []
for r in rows[3:]:
    if not r or len(r) < 6 or not r[5]:
        continue
    fn = str(r[1]).replace("'", "''").strip() if r[1] else ''
    ln = str(r[2]).replace("'", "''").strip() if r[2] else ''
    full = f"{fn} {ln}".strip()
    em = str(r[3]).replace("'", "''").strip().lower() if r[3] else ''
    ph = str(r[4]).replace("'", "''").strip() if r[4] else ''
    code = str(r[5]).replace("'", "''").strip()
    inserts.append(f"('{code}', '{fn}', '{ln}', '{full}', '{em}', '{ph}', 0, NOW(), NOW())")

sql = "INSERT INTO `pre_approved_agents` (`agent_code`, `first_name`, `last_name`, `full_name`, `email`, `phone_number`, `is_claimed`, `created_at`, `updated_at`) VALUES\n" + ",\n".join(inserts) + ";"

with open(r"c:\Users\hephz\Documents\CODEBASE\Fuwa.NG\pre_approved_agents_seed.sql", "w", encoding="utf-8") as f:
    f.write(sql)

print(f"Generated SQL for {len(inserts)} agents.")
