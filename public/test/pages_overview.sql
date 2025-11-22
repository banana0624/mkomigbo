SELECT
  s.name     AS subject_name,
  p.title,
  p.slug,
  p.visible,
  p.nav_order
FROM pages p
JOIN subjects s ON s.id = p.subject_id
ORDER BY s.nav_order, p.nav_order, p.id;
