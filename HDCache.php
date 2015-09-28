<?php
/*
** Copyright (c) Teleport corp 2012 - 2015 <richard@teleport.com.au>
** All Rights Reserved
**
** --
**
** LICENSE: Redistribution and use in source and binary forms, with or
** without modification, are permitted provided that the following
** conditions are met: Redistributions of source code must retain the
** above copyright notice, this list of conditions and the following
** disclaimer. Redistributions in binary form must reproduce the above
** copyright notice, this list of conditions and the following disclaimer
** in the documentation and/or other materials provided with the
** distribution.
**
** THIS SOFTWARE IS PROVIDED ``AS IS'' AND ANY EXPRESS OR IMPLIED
** WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF
** MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN
** NO EVENT SHALL CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
** INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
** BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS
** OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
** ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR
** TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE
** USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH
** DAMAGE.
*/

/**
 * Cache class for HandsetDetection - Uses file backed store
 *
 * Notes :
 *  - Cache objects may be > 1Mb when serialized which makes memcache a bad choice (1Mb limit).
 *  - Consider php-igbinary to improve serialization performance in time critical situations.
 *  - 48Mb of APC cache is optimal if you can spare it.
 **/

namespace HandsetDetection;

class HDCache {
	var $prefix = 'hd40';
	var $duration = 7200;

	function read($key) {
		return apc_fetch($this->prefix.$key);
	}

	function write($key, $data) {
		return apc_store($this->prefix.$key, $data, $this->duration);
	}

	function purge() {
		apc_clear_cache('user');
		return apc_clear_cache();
	}
}