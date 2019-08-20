ALTER TABLE `trace_sinks`
	ALTER `amount` DROP DEFAULT;
ALTER TABLE `trace_sinks`
	CHANGE COLUMN `amount` `amount` DECIMAL(10,2) NOT NULL;