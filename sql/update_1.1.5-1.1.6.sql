--
-- Script run to make a migration of module version x.x.x to module version y.y.y
--
UPDATE llx_funding_funding SET status_folder = 21 WHERE status_folder = 7;
UPDATE llx_funding_funding SET status_folder = 20 WHERE status_folder = 8;
