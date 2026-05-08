import secrets
import argon2

ph = argon2.PasswordHasher(
    time_cost=3,
    memory_cost=65536,
    parallelism=4,
    hash_len=32,
    salt_len=16,
)
suffix = secrets.token_urlsafe(6)
password = "Cdt!ra#9X$" + suffix
h = ph.hash(password)
print("PASSWORD:" + password)
print("HASH:" + h)
