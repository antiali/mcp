#!/bin/bash
# Test MCP Endpoints using curl

# Colors for output
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo "=== MCP Connection Test ==="
echo ""

# Get the site URL from WordPress (you may need to adjust this)
SITE_URL="https://projects.muhamedahmed.com/peralite2"
REST_BASE="${SITE_URL}/wp-json/awbu/v1"

echo "Testing endpoints on: ${REST_BASE}"
echo ""

# Test 1: GET /mcp/server-info
echo -e "${YELLOW}Test 1: GET /mcp/server-info${NC}"
RESPONSE=$(curl -s -w "\nHTTP_CODE:%{http_code}" "${REST_BASE}/mcp/server-info" -H "Content-Type: application/json")
HTTP_CODE=$(echo "$RESPONSE" | grep "HTTP_CODE" | cut -d: -f2)
BODY=$(echo "$RESPONSE" | sed '/HTTP_CODE/d')

if [ "$HTTP_CODE" = "200" ]; then
    echo -e "${GREEN}✅ Status: ${HTTP_CODE}${NC}"
    if echo "$BODY" | grep -q "serverInfo"; then
        echo -e "${GREEN}✅ Server Info Found!${NC}"
        echo "$BODY" | python -m json.tool 2>/dev/null || echo "$BODY"
    else
        echo -e "${RED}❌ Server Info Missing!${NC}"
        echo "$BODY"
    fi
else
    echo -e "${RED}❌ Status: ${HTTP_CODE}${NC}"
    echo "$BODY"
fi
echo ""

# Test 2: POST /mcp/server-info
echo -e "${YELLOW}Test 2: POST /mcp/server-info${NC}"
RESPONSE=$(curl -s -w "\nHTTP_CODE:%{http_code}" -X POST "${REST_BASE}/mcp/server-info" \
    -H "Content-Type: application/json" \
    -d '{"method":"server-info"}')
HTTP_CODE=$(echo "$RESPONSE" | grep "HTTP_CODE" | cut -d: -f2)
BODY=$(echo "$RESPONSE" | sed '/HTTP_CODE/d')

if [ "$HTTP_CODE" = "200" ]; then
    echo -e "${GREEN}✅ Status: ${HTTP_CODE}${NC}"
    if echo "$BODY" | grep -q "serverInfo"; then
        echo -e "${GREEN}✅ Server Info Found!${NC}"
        echo "$BODY" | python -m json.tool 2>/dev/null || echo "$BODY"
    else
        echo -e "${RED}❌ Server Info Missing!${NC}"
        echo "$BODY"
    fi
else
    echo -e "${RED}❌ Status: ${HTTP_CODE}${NC}"
    echo "$BODY"
fi
echo ""

# Test 3: GET /mcp/serverInfo (camelCase)
echo -e "${YELLOW}Test 3: GET /mcp/serverInfo (camelCase)${NC}"
RESPONSE=$(curl -s -w "\nHTTP_CODE:%{http_code}" "${REST_BASE}/mcp/serverInfo" -H "Content-Type: application/json")
HTTP_CODE=$(echo "$RESPONSE" | grep "HTTP_CODE" | cut -d: -f2)
BODY=$(echo "$RESPONSE" | sed '/HTTP_CODE/d')

if [ "$HTTP_CODE" = "200" ]; then
    echo -e "${GREEN}✅ Status: ${HTTP_CODE}${NC}"
    if echo "$BODY" | grep -q "serverInfo"; then
        echo -e "${GREEN}✅ Alternative Endpoint Works!${NC}"
        echo "$BODY" | python -m json.tool 2>/dev/null || echo "$BODY"
    else
        echo -e "${RED}❌ Server Info Missing!${NC}"
        echo "$BODY"
    fi
else
    echo -e "${RED}❌ Status: ${HTTP_CODE}${NC}"
    echo "$BODY"
fi
echo ""

# Test 4: POST /mcp/initialize
echo -e "${YELLOW}Test 4: POST /mcp/initialize${NC}"
RESPONSE=$(curl -s -w "\nHTTP_CODE:%{http_code}" -X POST "${REST_BASE}/mcp/initialize" \
    -H "Content-Type: application/json" \
    -d '{
        "protocolVersion": "2024-11-05",
        "capabilities": {},
        "clientInfo": {
            "name": "Test Client",
            "version": "1.0.0"
        }
    }')
HTTP_CODE=$(echo "$RESPONSE" | grep "HTTP_CODE" | cut -d: -f2)
BODY=$(echo "$RESPONSE" | sed '/HTTP_CODE/d')

if [ "$HTTP_CODE" = "200" ]; then
    echo -e "${GREEN}✅ Status: ${HTTP_CODE}${NC}"
    if echo "$BODY" | grep -q "serverInfo"; then
        echo -e "${GREEN}✅ Initialize Success!${NC}"
        echo "$BODY" | python -m json.tool 2>/dev/null || echo "$BODY"
    else
        echo -e "${RED}❌ Initialize Failed - Missing serverInfo!${NC}"
        echo "$BODY"
    fi
else
    echo -e "${RED}❌ Status: ${HTTP_CODE}${NC}"
    echo "$BODY"
fi
echo ""

# Test 5: POST /mcp (generic handler)
echo -e "${YELLOW}Test 5: POST /mcp (with method: server-info)${NC}"
RESPONSE=$(curl -s -w "\nHTTP_CODE:%{http_code}" -X POST "${REST_BASE}/mcp" \
    -H "Content-Type: application/json" \
    -d '{"method":"server-info"}')
HTTP_CODE=$(echo "$RESPONSE" | grep "HTTP_CODE" | cut -d: -f2)
BODY=$(echo "$RESPONSE" | sed '/HTTP_CODE/d')

if [ "$HTTP_CODE" = "200" ]; then
    echo -e "${GREEN}✅ Status: ${HTTP_CODE}${NC}"
    if echo "$BODY" | grep -q "serverInfo"; then
        echo -e "${GREEN}✅ Generic Handler Works!${NC}"
        echo "$BODY" | python -m json.tool 2>/dev/null || echo "$BODY"
    else
        echo -e "${RED}❌ Generic Handler Failed!${NC}"
        echo "$BODY"
    fi
else
    echo -e "${RED}❌ Status: ${HTTP_CODE}${NC}"
    echo "$BODY"
fi
echo ""

echo "=== Test Complete ==="

