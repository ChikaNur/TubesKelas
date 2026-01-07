-- Migration: Add feedback column to submissions table
-- Date: 2026-01-08
-- Description: Add feedback column to store instructor feedback for graded assignments

ALTER TABLE submissions ADD COLUMN feedback TEXT AFTER score;
