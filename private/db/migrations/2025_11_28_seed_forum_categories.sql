-- project-root/private/db/migrations/2025_11_28_seed_forum_categories.sql
-- =====================================================================
-- Seed: initial forum categories
-- Adjust slugs/titles if needed before running.
-- =====================================================================

INSERT INTO forum_categories (slug, title, description, position, is_public)
VALUES
  (
    'history-interpretation',
    'History & interpretation',
    'Questions, debates and clarifications about the history of Ndi Mkomigbo and related events.',
    10,
    1
  ),
  (
    'language-expression',
    'Language & expression',
    'Discussions on words, spellings, proverbs and the living use of language in Mkomigbo and wider Igbo contexts.',
    20,
    1
  ),
  (
    'culture-belief-life',
    'Culture, belief & everyday life',
    'Topics about customs, beliefs, practices and how they appear in daily life, past and present.',
    30,
    1
  ),
  (
    'sources-archives-research',
    'Sources, archives & research',
    'Sharing and examining books, articles, recordings and archives that help document our story.',
    40,
    1
  );